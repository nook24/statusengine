<?php
/**********************************************************************************
*
*    #####
*   #     # #####   ##   ##### #    #  ####  ###### #    #  ####  # #    # ######
*   #         #    #  #    #   #    # #      #      ##   # #    # # ##   # #
*    #####    #   #    #   #   #    #  ####  #####  # #  # #      # # #  # #####
*         #   #   ######   #   #    #      # #      #  # # #  ### # #  # # #
*   #     #   #   #    #   #   #    # #    # #      #   ## #    # # #   ## #
*    #####    #   #    #   #    ####   ####  ###### #    #  ####  # #    # ######
*
*                            the missing event broker
*
* --------------------------------------------------------------------------------
*
* Copyright (c) 2014 - present Daniel Ziegler <daniel@statusengine.org>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation in version 2
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*
* --------------------------------------------------------------------------------
*
* This file defines the definition of the type attribute you get from Nagios/Naemon core.
* Examples:
* stdClass Object
* (
*     [type] => 900 <--
*     [flags] => 0
*     [attr] => 0
*     [timestamp] => 1474370689
*     [comment] => stdClass Object => (...)
* )
* 
* stdClass Object
* (
*     [type] => 902 <--
*     [flags] => 0
*     [attr] => 0
*     [timestamp] => 1474370689
*     [comment] => stdClass Object => (...)
* )
*
* All constants that are not used are flaged. If you implement them, please remove
* the flag as well.
**********************************************************************************/

class NebTypes{
	
	public $types = [
		'NEBTYPE_NONE'                            =>     0, // NOT USED

		'NEBTYPE_HELLO'                           =>     1, // NOT USED
		'NEBTYPE_GOODBYE'                         =>     2, // NOT USED
		'NEBTYPE_INFO'                            =>     3, // NOT USED

		'NEBTYPE_PROCESS_START'                   =>   100, // NOT USED
		'NEBTYPE_PROCESS_DAEMONIZE'               =>   101, // NOT USED
		'NEBTYPE_PROCESS_RESTART'                 =>   102, // NOT USED
		'NEBTYPE_PROCESS_SHUTDOWN'                =>   103, // NOT USED
		'NEBTYPE_PROCESS_PRELAUNCH'               =>   104, // NOT USED
		'NEBTYPE_PROCESS_EVENTLOOPSTART'          =>   105, // NOT USED
		'NEBTYPE_PROCESS_EVENTLOOPEND'            =>   106, // NOT USED

		'NEBTYPE_TIMEDEVENT_ADD'                  =>   200, // NOT USED
		'NEBTYPE_TIMEDEVENT_REMOVE'               =>   201, // NOT USED
		'NEBTYPE_TIMEDEVENT_EXECUTE'              =>   202, // NOT USED
		'NEBTYPE_TIMEDEVENT_DELAY'                =>   203, // NOT USED
		'NEBTYPE_TIMEDEVENT_SKIP'                 =>   204, // NOT USED
		'NEBTYPE_TIMEDEVENT_SLEEP'                =>   205, // NOT USED

		'NEBTYPE_LOG_DATA'                        =>   300, // NOT USED
		'NEBTYPE_LOG_ROTATION'                    =>   301, // NOT USED

		'NEBTYPE_SYSTEM_COMMAND_START'            =>   400, // NOT USED
		'NEBTYPE_SYSTEM_COMMAND_END'              =>   401, // NOT USED

		'NEBTYPE_EVENTHANDLER_START'              =>   500, // NOT USED
		'NEBTYPE_EVENTHANDLER_END'                =>   501, // NOT USED

		'NEBTYPE_NOTIFICATION_START'              =>   600, // NOT USED
		'NEBTYPE_NOTIFICATION_END'                =>   601, // NOT USED
		'NEBTYPE_CONTACTNOTIFICATION_START'       =>   602, // NOT USED
		'NEBTYPE_CONTACTNOTIFICATION_END'         =>   603, // NOT USED
		'NEBTYPE_CONTACTNOTIFICATIONMETHOD_START' =>   604, // NOT USED
		'NEBTYPE_CONTACTNOTIFICATIONMETHOD_END'   =>   605, // NOT USED

		'NEBTYPE_SERVICECHECK_INITIATE'           =>   700, // NOT USED
		'NEBTYPE_SERVICECHECK_PROCESSED'          =>   701, // NOT USED
		'NEBTYPE_SERVICECHECK_RAW_START'          =>   702, // NOT USED
		'NEBTYPE_SERVICECHECK_RAW_END'            =>   703, // NOT USED
		'NEBTYPE_SERVICECHECK_ASYNC_PRECHECK'     =>   704, // NOT USED

		'NEBTYPE_HOSTCHECK_INITIATE'              =>   800, // NOT USED
		'NEBTYPE_HOSTCHECK_PROCESSED'             =>   801, // NOT USED
		'NEBTYPE_HOSTCHECK_RAW_START'             =>   802,
		'NEBTYPE_HOSTCHECK_RAW_END'               =>   803,
		'NEBTYPE_HOSTCHECK_ASYNC_PRECHECK'        =>   804, // NOT USED
		'NEBTYPE_HOSTCHECK_SYNC_PRECHECK'         =>   805, // NOT USED

		'NEBTYPE_COMMENT_ADD'                     =>   900,
		'NEBTYPE_COMMENT_DELETE'                  =>   901,
		'NEBTYPE_COMMENT_LOAD'                    =>   902,

		'NEBTYPE_FLAPPING_START'                  =>  1000, // NOT USED
		'NEBTYPE_FLAPPING_STOP'                   =>  1001, // NOT USED

		'NEBTYPE_DOWNTIME_ADD'                    =>  1100,
		'NEBTYPE_DOWNTIME_DELETE'                 =>  1101, // NOT USED
		'NEBTYPE_DOWNTIME_LOAD'                   =>  1102,
		'NEBTYPE_DOWNTIME_START'                  =>  1103,
		'NEBTYPE_DOWNTIME_STOP'                   =>  1104,

		'NEBTYPE_PROGRAMSTATUS_UPDATE'            =>  1200, // NOT USED
		'NEBTYPE_HOSTSTATUS_UPDATE'               =>  1201, // NOT USED
		'NEBTYPE_SERVICESTATUS_UPDATE'            =>  1202, // NOT USED
		'NEBTYPE_CONTACTSTATUS_UPDATE'            =>  1203, // NOT USED

		'NEBTYPE_ADAPTIVEPROGRAM_UPDATE'          =>  1300, // NOT USED
		'NEBTYPE_ADAPTIVEHOST_UPDATE'             =>  1301, // NOT USED
		'NEBTYPE_ADAPTIVESERVICE_UPDATE'          =>  1302, // NOT USED
		'NEBTYPE_ADAPTIVECONTACT_UPDATE'          =>  1303, // NOT USED

		'NEBTYPE_EXTERNALCOMMAND_START'           =>  1400, // NOT USED
		'NEBTYPE_EXTERNALCOMMAND_END'             =>  1401, // NOT USED

		'NEBTYPE_AGGREGATEDSTATUS_STARTDUMP'      =>  1500, // NOT USED
		'NEBTYPE_AGGREGATEDSTATUS_ENDDUMP'        =>  1501, // NOT USED

		'NEBTYPE_RETENTIONDATA_STARTLOAD'         =>  1600, // NOT USED
		'NEBTYPE_RETENTIONDATA_ENDLOAD'           =>  1601, // NOT USED
		'NEBTYPE_RETENTIONDATA_STARTSAVE'         =>  1602, // NOT USED
		'NEBTYPE_RETENTIONDATA_ENDSAVE'           =>  1603, // NOT USED

		'NEBTYPE_ACKNOWLEDGEMENT_ADD'             =>  1700, // NOT USED
		'NEBTYPE_ACKNOWLEDGEMENT_REMOVE'          =>  1701, // NOT USED
		'NEBTYPE_ACKNOWLEDGEMENT_LOAD'            =>  1702, // NOT USED

		'NEBTYPE_STATECHANGE_START'               =>  1800, // NOT USED
		'NEBTYPE_STATECHANGE_END'                 =>  1801, // NOT USED
	];
	
	public function defineNebTypesAsGlobals(){
		foreach($this->types as $name => $value){
			if(!defined($name)){
				define($name, $value);
			}
		}
	}
	
}