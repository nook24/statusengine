<?php
class LegacySchema extends CakeSchema {

	/* Useful schema commands:
	 *
	 * Update to new schema:
	 *  ../../cakephp/app/Console/cake schema update
	 *
	 * Update to snapshot:
	 * /opt/statusengine/cakephp/app/Console/cake schema update --plugin Legacy --file legacy_schema.php --connection legacy -s X
	 *
	 * Generate new snapshot
	 * 	/opt/statusengine/cakephp/app/Console/cake schema generate --plugin Legacy --file legacy_schema_X.php --connection legacy
	 *
	 * Based on NDO database schema
         * Copyright 1999-2009:
         *   Ethan Galstad <egalstad@nagios.org>
         * Copyright 2009 until further notice:
         *   Nagios Core Development Team and Nagios Community Contributors
         * GNU GENERAL PUBLIC LICENSE Version 2, June 1991
	 *
	 */

	public $connection = 'legacy';

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $acknowledgements = array(
		'acknowledgement_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'entry_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'entry_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'acknowledgement_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'state' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'author_name' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'comment_data' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'is_sticky' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'persistent_comment' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_contacts' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'acknowledgement_id', 'unique' => 1),
			'entry_time' => array('column' => 'entry_time', 'unique' => 0),
			'instance_id' => array('column' => array('instance_id', 'object_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $commands = array(
		'command_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'command_line' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 511, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'command_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'object_id', 'config_type'), 'unique' => 1),
			'object_id' => array('column' => 'object_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $commenthistory = array(
		'commenthistory_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'entry_time' => array('type' => 'datetime', 'null' => true, 'default' => '1970-01-01 00:00:00'),
		'entry_time_usec' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false),
		'comment_type' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'entry_type' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'object_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false),
		'comment_time' => array('type' => 'datetime', 'null' => true, 'default' => '1970-01-01 00:00:00'),
		'internal_comment_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false),
		'author_name' => array('type' => 'string', 'null' => true, 'length' => 64, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'comment_data' => array('type' => 'string', 'null' => true, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'is_persistent' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'comment_source' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'expires' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'expiration_time' => array('type' => 'datetime', 'null' => true, 'default' => '1970-01-01 00:00:00'),
		'deletion_time' => array('type' => 'datetime', 'null' => true, 'default' => '1970-01-01 00:00:00'),
		'deletion_time_usec' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'commenthistory_id', 'unique' => 1),
			'object_id_internal_comment_id' => array('column' => array('object_id', 'internal_comment_id'), 'unique' => 1),
			'object_id' => array('column' => array('object_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM', 'comment' => 'Historical host and service comments')
	);

	public $comments = array(
		'comment_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'entry_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'entry_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'comment_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'entry_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'comment_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'internal_comment_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'author_name' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'comment_data' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'is_persistent' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'comment_source' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'expires' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'expiration_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'comment_id', 'unique' => 1),
			'object_id_internal_comment_id' => array('column' => array('object_id', 'internal_comment_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $configfiles = array(
		'configfile_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'configfile_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'configfile_path' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'configfile_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'configfile_type', 'configfile_path'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $configfilevariables = array(
		'configfilevariable_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'configfile_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'varname' => array('type' => 'string', 'null' => false, 'length' => 255, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'varvalue' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8', 'length' => 1024),
		'indexes' => array(
			'PRIMARY' => array('column' => 'configfilevariable_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'configfile_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $contact_addresses = array(
		'contact_address_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'contact_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'address_number' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'address' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'contact_address_id', 'unique' => 1),
			'contact_id' => array('column' => array('contact_id', 'address_number'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $contact_notificationcommands = array(
		'contact_notificationcommand_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'contact_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'notification_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'command_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'command_args' => array('type' => 'string', 'null' => false, 'length' => 1000, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'contact_notificationcommand_id', 'unique' => 1),
			'contact_id' => array('column' => array('contact_id', 'notification_type', 'command_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $contactgroup_members = array(
		'contactgroup_member_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'contactgroup_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'contact_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'contactgroup_member_id', 'unique' => 1),
			'instance_id' => array('column' => array('contactgroup_id', 'contact_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $contactgroups = array(
		'contactgroup_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'contactgroup_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'alias' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'contactgroup_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'config_type', 'contactgroup_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $contactnotificationmethods = array(
		'contactnotificationmethod_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'contactnotification_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'start_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'end_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'end_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'command_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'command_args' => array('type' => 'string', 'null' => true, 'length' => 1000, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'contactnotificationmethod_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'contactnotification_id', 'start_time', 'start_time_usec'), 'unique' => 1),
			'start_time' => array('column' => 'start_time', 'unique' => 0),
			'command_object_id' => array('column' => 'command_object_id', 'unique' => 0),
			'contactnotification_id' => array('column' => 'contactnotification_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $contactnotifications = array(
		'contactnotification_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notification_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'contact_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'start_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'end_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'end_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'contactnotification_id', 'unique' => 1),
			'start_time' => array('column' => 'start_time', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $contacts = array(
		'contact_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'contact_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'alias' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'email_address' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'pager_address' => array('type' => 'string', 'null' => true, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'host_timeperiod_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'service_timeperiod_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'host_notifications_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'service_notifications_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'can_submit_commands' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_service_recovery' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_service_warning' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_service_unknown' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_service_critical' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_service_flapping' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_service_downtime' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_host_recovery' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_host_down' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_host_unreachable' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_host_flapping' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_host_downtime' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'minimum_importance' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'contact_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'config_type', 'contact_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $contactstatus = array(
		'contactstatus_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'contact_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'unique'),
		'status_update_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'host_notifications_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'service_notifications_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'last_host_notification' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'last_service_notification' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'modified_attributes' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'modified_host_attributes' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'modified_service_attributes' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'contactstatus_id', 'unique' => 1),
			'contact_object_id' => array('column' => 'contact_object_id', 'unique' => 1),
			'instance_id' => array('column' => 'contactstatus_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $customvariables = array(
		'customvariable_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'has_been_modified' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'varname' => array('type' => 'string', 'null' => false, 'key' => 'index', 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'varvalue' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'customvariable_id', 'unique' => 1),
			'object_id_2' => array('column' => array('object_id', 'config_type', 'varname'), 'unique' => 1),
			'varname' => array('column' => 'varname', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $dbversion = array(
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 12, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8', 'key' => 'primary'),
		'version' => array('type' => 'string', 'null' => false, 'length' => 10, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(

		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $downtimehistory = array(
		'downtimehistory_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'downtime_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'entry_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'author_name' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'comment_data' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'internal_downtime_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'triggered_by_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'is_fixed' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'duration' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'scheduled_start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'scheduled_end_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'was_started' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'actual_start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'actual_start_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'actual_end_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'actual_end_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'was_cancelled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'downtimehistory_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'object_id', 'entry_time', 'internal_downtime_id'), 'unique' => 1),
			'scheduled_start_time' => array('column' => 'scheduled_start_time', 'unique' => 0),
			'scheduled_end_time' => array('column' => 'scheduled_end_time', 'unique' => 0),
			'object_id' => array('column' => 'object_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $eventhandlers = array(
		'eventhandler_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'eventhandler_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'state' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'state_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'start_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'end_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'end_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'command_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'command_args' => array('type' => 'string', 'null' => true, 'length' => 1000, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'command_line' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'timeout' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'early_timeout' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'execution_time' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'return_code' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'output' => array('type' => 'string', 'null' => true, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'long_output' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'eventhandler_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $externalcommands = array(
		'externalcommand_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'entry_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'command_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'command_name' => array('type' => 'string', 'null' => false, 'length' => 128, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'command_args' => array('type' => 'string', 'null' => true, 'length' => 1000, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'externalcommand_id', 'unique' => 1),
			'instance_id' => array('column' => 'instance_id', 'unique' => 0),
			'entry_time' => array('column' => 'entry_time', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $flappinghistory = array(
		'flappinghistory_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'event_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'event_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'event_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'reason_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'flapping_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'percent_state_change' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'low_threshold' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'high_threshold' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'comment_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'internal_comment_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'flappinghistory_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'object_id'), 'unique' => 0),
			'object_id' => array('column' => array('object_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $host_contactgroups = array(
		'host_contactgroup_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'host_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'contactgroup_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'host_contactgroup_id', 'unique' => 1),
			'instance_id' => array('column' => array('host_id', 'contactgroup_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $host_contacts = array(
		'host_contact_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'host_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'contact_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'host_contact_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'host_id', 'contact_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $host_parenthosts = array(
		'host_parenthost_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'host_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'parent_host_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'host_parenthost_id', 'unique' => 1),
			'instance_id' => array('column' => array('host_id', 'parent_host_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $hostchecks = array(
		'hostcheck_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'host_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'check_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'is_raw_check' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'current_check_attempt' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'max_check_attempts' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'state' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'state_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'start_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'end_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'end_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'command_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'command_args' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'command_line' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'timeout' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'early_timeout' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'execution_time' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'latency' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'return_code' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'output' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'long_output' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'perfdata' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'hostcheck_id', 'unique' => 1),
			'start_time' => array('column' => 'start_time', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $hostdependencies = array(
		'hostdependency_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'host_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'dependent_host_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'dependency_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'inherits_parent' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'timeperiod_object_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false),
		'fail_on_up' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'fail_on_down' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'fail_on_unreachable' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'hostdependency_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'config_type', 'host_object_id', 'dependent_host_object_id', 'dependency_type', 'inherits_parent', 'fail_on_up', 'fail_on_down', 'fail_on_unreachable'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $hostescalation_contactgroups = array(
		'hostescalation_contactgroup_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'hostescalation_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'contactgroup_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'hostescalation_contactgroup_id', 'unique' => 1),
			'instance_id' => array('column' => array('hostescalation_id', 'contactgroup_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $hostescalation_contacts = array(
		'hostescalation_contact_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'hostescalation_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'contact_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'hostescalation_contact_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'hostescalation_id', 'contact_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $hostescalations = array(
		'hostescalation_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'host_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'timeperiod_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'first_notification' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'last_notification' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notification_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'escalate_on_recovery' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'escalate_on_down' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'escalate_on_unreachable' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'hostescalation_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'config_type', 'host_object_id', 'timeperiod_object_id', 'first_notification', 'last_notification'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $hostgroup_members = array(
		'hostgroup_member_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'hostgroup_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'host_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'hostgroup_member_id', 'unique' => 1),
			'instance_id' => array('column' => array('hostgroup_id', 'host_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $hostgroups = array(
		'hostgroup_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'hostgroup_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'alias' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'hostgroup_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'hostgroup_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $hosts = array(
		'host_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'host_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'alias' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'display_name' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'address' => array('type' => 'string', 'null' => false, 'length' => 128, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'check_command_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'check_command_args' => array('type' => 'string', 'null' => true, 'length' => 1000, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'eventhandler_command_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'eventhandler_command_args' => array('type' => 'string', 'null' => true, 'length' => 1000, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'notification_timeperiod_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'check_timeperiod_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'failure_prediction_options' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'check_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'retry_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'max_check_attempts' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'first_notification_delay' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'notification_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'notify_on_down' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_on_unreachable' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_on_recovery' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_on_flapping' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_on_downtime' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'stalk_on_up' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'stalk_on_down' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'stalk_on_unreachable' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'flap_detection_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'flap_detection_on_up' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'flap_detection_on_down' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'flap_detection_on_unreachable' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'low_flap_threshold' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'high_flap_threshold' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'process_performance_data' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'freshness_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'freshness_threshold' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'unsigned' => false),
		'passive_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'event_handler_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'active_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'retain_status_information' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'retain_nonstatus_information' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notifications_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'obsess_over_host' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'failure_prediction_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notes' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'notes_url' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'action_url' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'icon_image' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'icon_image_alt' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'vrml_image' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'statusmap_image' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'have_2d_coords' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'x_2d' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'y_2d' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'have_3d_coords' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'x_3d' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'y_3d' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'z_3d' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'importance' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'host_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'config_type', 'host_object_id'), 'unique' => 1),
			'host_object_id' => array('column' => 'host_object_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $hoststatus = array(
		'hoststatus_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'host_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'unique'),
		'status_update_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'output' => array('type' => 'string', 'null' => true, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'long_output' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'perfdata' => array('type' => 'string', 'null' => true, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'current_state' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'has_been_checked' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'should_be_scheduled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'current_check_attempt' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'max_check_attempts' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'last_check' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'next_check' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'check_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'last_state_change' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'last_hard_state_change' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'last_hard_state' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'last_time_up' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'last_time_down' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'last_time_unreachable' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'state_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'last_notification' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'next_notification' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'no_more_notifications' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notifications_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'problem_has_been_acknowledged' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'acknowledgement_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'current_notification_number' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'passive_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'active_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'event_handler_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'flap_detection_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'is_flapping' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'percent_state_change' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'latency' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'execution_time' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'scheduled_downtime_depth' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'failure_prediction_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'process_performance_data' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'obsess_over_host' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'modified_host_attributes' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'event_handler' => array('type' => 'string', 'null' => true, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'check_command' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'normal_check_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'retry_check_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'check_timeperiod_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'hoststatus_id', 'unique' => 1),
			'object_id_instance_id' => array('column' => array('host_object_id', 'instance_id'), 'unique' => 1),
			'hoststatus' => array('column' => array('host_object_id', 'current_state'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $instances = array(
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 6, 'unsigned' => false, 'key' => 'primary'),
		'instance_name' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'instance_description' => array('type' => 'string', 'null' => false, 'length' => 128, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'instance_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $logentries = array(
		'logentry_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'logentry_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'entry_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'entry_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'logentry_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'logentry_data' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'realtime_data' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'inferred_data_extracted' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'logentry_id', 'unique' => 1),
			'logentry_time' => array('column' => 'logentry_time', 'unique' => 0),
			'legentry_time' => array('column' => 'logentry_time', 'unique' => 0),
			'entry_time' => array('column' => 'entry_time', 'unique' => 0),
			'entry_time_usec' => array('column' => 'entry_time_usec', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $notifications = array(
		'notification_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notification_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notification_reason' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'start_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'end_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'end_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'state' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'output' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'long_output' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'escalated' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'contacts_notified' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'notification_id', 'unique' => 1),
			'top10' => array('column' => array('object_id', 'start_time', 'contacts_notified'), 'unique' => 0),
			'start_time' => array('column' => 'start_time', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $objects = array(
		'object_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'objecttype_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'name1' => array('type' => 'string', 'null' => false, 'length' => 128, 'key' => 'index', 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'name2' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'is_active' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'object_id', 'unique' => 1),
			'objecttype_id' => array('column' => array('objecttype_id', 'name1', 'name2'), 'unique' => 0),
			'achmet' => array('column' => array('name1', 'name2'), 'unique' => 0),
			'nag_name1' => array('column' => 'name1', 'unique' => 0),
			'nag_name1_and_name2' => array('column' => array('name1', 'name2'), 'unique' => 0),
			'check_object' => array('column' => array('objecttype_id', 'name1', 'name2', 'instance_id', 'is_active', 'object_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $processevents = array(
		'processevent_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'event_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'event_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'event_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'process_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'program_name' => array('type' => 'string', 'null' => false, 'length' => 16, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'program_version' => array('type' => 'string', 'null' => false, 'length' => 20, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'program_date' => array('type' => 'string', 'null' => false, 'length' => 10, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'processevent_id', 'unique' => 1),
			'event_time' => array('column' => array('event_time', 'event_time_usec'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $programstatus = array(
		'programstatus_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'unique'),
		'status_update_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'program_start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'program_end_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'is_currently_running' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'process_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'daemon_mode' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'last_command_check' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'last_log_rotation' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'notifications_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'active_service_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'passive_service_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'active_host_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'passive_host_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'event_handlers_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'flap_detection_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'failure_prediction_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'process_performance_data' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'obsess_over_hosts' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'obsess_over_services' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'modified_host_attributes' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false),
		'modified_service_attributes' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false),
		'global_host_event_handler' => array('type' => 'string', 'null' => true, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'global_service_event_handler' => array('type' => 'string', 'null' => true, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'programstatus_id', 'unique' => 1),
			'instance_id' => array('column' => 'instance_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $scheduleddowntime = array(
		'scheduleddowntime_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'downtime_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'entry_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'author_name' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'comment_data' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'internal_downtime_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'triggered_by_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'is_fixed' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'duration' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'scheduled_start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'scheduled_end_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'was_started' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'actual_start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'actual_start_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'scheduleddowntime_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'object_id', 'entry_time', 'internal_downtime_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $service_contactgroups = array(
		'service_contactgroup_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'service_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'contactgroup_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'service_contactgroup_id', 'unique' => 1),
			'instance_id' => array('column' => array('service_id', 'contactgroup_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $service_contacts = array(
		'service_contact_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'service_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'contact_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'service_contact_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'service_id', 'contact_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $service_parentservices = array(
		'service_parentservice_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'service_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'parent_service_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'service_parentservice_id', 'unique' => 1),
			'instance_id' => array('column' => array('service_id', 'parent_service_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $servicechecks = array(
		'servicecheck_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'service_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'check_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'current_check_attempt' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'max_check_attempts' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'state' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'state_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'start_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'end_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'end_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'command_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'command_args' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'command_line' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'timeout' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'early_timeout' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'execution_time' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'latency' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'return_code' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'output' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1000, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'long_output' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'perfdata' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1000, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'servicecheck_id', 'unique' => 1),
			'start_time' => array('column' => 'start_time', 'unique' => 0),
			'service_object_id' => array('column' => 'service_object_id', 'unique' => 0),
			'start_time_2' => array('column' => 'start_time', 'unique' => 0),
			'instance_id' => array('column' => 'instance_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $servicedependencies = array(
		'servicedependency_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'service_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'dependent_service_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'dependency_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'inherits_parent' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'timeperiod_object_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false),
		'fail_on_ok' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'fail_on_warning' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'fail_on_unknown' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'fail_on_critical' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'servicedependency_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'config_type', 'service_object_id', 'dependent_service_object_id', 'dependency_type', 'inherits_parent', 'fail_on_ok', 'fail_on_warning', 'fail_on_unknown', 'fail_on_critical'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $serviceescalation_contactgroups = array(
		'serviceescalation_contactgroup_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'serviceescalation_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'contactgroup_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'serviceescalation_contactgroup_id', 'unique' => 1),
			'instance_id' => array('column' => array('serviceescalation_id', 'contactgroup_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $serviceescalation_contacts = array(
		'serviceescalation_contact_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'serviceescalation_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'contact_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'serviceescalation_contact_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'serviceescalation_id', 'contact_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $serviceescalations = array(
		'serviceescalation_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'service_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'timeperiod_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'first_notification' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'last_notification' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notification_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'escalate_on_recovery' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'escalate_on_warning' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'escalate_on_unknown' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'escalate_on_critical' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'serviceescalation_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'config_type', 'service_object_id', 'timeperiod_object_id', 'first_notification', 'last_notification'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $servicegroup_members = array(
		'servicegroup_member_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'servicegroup_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'service_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'servicegroup_member_id', 'unique' => 1),
			'instance_id' => array('column' => array('servicegroup_id', 'service_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $servicegroups = array(
		'servicegroup_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'servicegroup_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'alias' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'servicegroup_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'config_type', 'servicegroup_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $services = array(
		'service_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'host_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'service_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'display_name' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'check_command_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'check_command_args' => array('type' => 'string', 'null' => true, 'length' => 1000, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'eventhandler_command_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'eventhandler_command_args' => array('type' => 'string', 'null' => true, 'length' => 1000, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'notification_timeperiod_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'check_timeperiod_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'failure_prediction_options' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'check_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'retry_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'max_check_attempts' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'first_notification_delay' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'notification_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'notify_on_warning' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_on_unknown' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_on_critical' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_on_recovery' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_on_flapping' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notify_on_downtime' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'stalk_on_ok' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'stalk_on_warning' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'stalk_on_unknown' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'stalk_on_critical' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'is_volatile' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'flap_detection_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'flap_detection_on_ok' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'flap_detection_on_warning' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'flap_detection_on_unknown' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'flap_detection_on_critical' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'low_flap_threshold' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'high_flap_threshold' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'process_performance_data' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'freshness_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'unsigned' => false),
		'freshness_threshold' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'passive_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'event_handler_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'active_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'retain_status_information' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'retain_nonstatus_information' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notifications_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'obsess_over_service' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'failure_prediction_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notes' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'notes_url' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'action_url' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'icon_image' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'icon_image_alt' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'importance' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'service_id', 'unique' => 1),
			'service_object_id' => array('column' => 'service_object_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $servicestatus = array(
		'servicestatus_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'service_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'unique'),
		'status_update_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'output' => array('type' => 'string', 'null' => true, 'length' => 512, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'long_output' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'perfdata' => array('type' => 'string', 'null' => true, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'current_state' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'has_been_checked' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'should_be_scheduled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'current_check_attempt' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'max_check_attempts' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'last_check' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'next_check' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'check_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'last_state_change' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'last_hard_state_change' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'last_hard_state' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'last_time_ok' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'last_time_warning' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'last_time_unknown' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'last_time_critical' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'state_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'last_notification' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'next_notification' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'no_more_notifications' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'notifications_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'problem_has_been_acknowledged' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'acknowledgement_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'current_notification_number' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'passive_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'active_checks_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'event_handler_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'flap_detection_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'is_flapping' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'percent_state_change' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'latency' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'execution_time' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'scheduled_downtime_depth' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'failure_prediction_enabled' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'process_performance_data' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'obsess_over_service' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'modified_service_attributes' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'event_handler' => array('type' => 'string', 'null' => true, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'check_command' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'normal_check_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'retry_check_interval' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'check_timeperiod_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'servicestatus_id', 'unique' => 1),
			'object_id_instance_id' => array('column' => array('service_object_id', 'instance_id'), 'unique' => 1),
			'servicestatus' => array('column' => array('service_object_id', 'current_state', 'last_check', 'next_check', 'last_hard_state_change', 'output', 'scheduled_downtime_depth', 'active_checks_enabled', 'state_type', 'problem_has_been_acknowledged', 'is_flapping'), 'unique' => 0, 'length' => array('output' => '255'))
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $statehistory = array(
		'statehistory_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'state_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00', 'key' => 'index'),
		'state_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'state_change' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'state' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'state_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'current_check_attempt' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'max_check_attempts' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'last_state' => array('type' => 'integer', 'null' => false, 'default' => '-1', 'length' => 6, 'unsigned' => false),
		'last_hard_state' => array('type' => 'integer', 'null' => false, 'default' => '-1', 'length' => 6, 'unsigned' => false),
		'output' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'long_output' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'statehistory_id', 'unique' => 1),
			'state_time' => array('column' => array('state_time', 'state_time_usec'), 'unique' => 0),
			'object_id' => array('column' => array('object_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $systemcommands = array(
		'systemcommand_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'start_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'start_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'end_time' => array('type' => 'datetime', 'null' => false, 'default' => '1970-01-01 00:00:00'),
		'end_time_usec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'command_line' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'timeout' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'early_timeout' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'execution_time' => array('type' => 'float', 'null' => false, 'default' => '0', 'unsigned' => false),
		'return_code' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'output' => array('type' => 'string', 'null' => true, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'long_output' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'systemcommand_id', 'unique' => 1),
			'instance_id' => array('column' => 'instance_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $timeperiod_timeranges = array(
		'timeperiod_timerange_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'timeperiod_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'day' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'start_sec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'end_sec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'timeperiod_timerange_id', 'unique' => 1),
			'instance_id' => array('column' => array('timeperiod_id', 'day', 'start_sec', 'end_sec'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

	public $timeperiods = array(
		'timeperiod_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'instance_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'config_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false),
		'timeperiod_object_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'alias' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'timeperiod_id', 'unique' => 1),
			'instance_id' => array('column' => array('instance_id', 'config_type', 'timeperiod_object_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'MyISAM')
	);

}
