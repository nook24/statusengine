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
*                              Memcached Extension
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
* With Statusengine Memcached extension you save host status, service status,
* acknowledgement and downtime data objects in a memory based database
* using Memcached
* All other status data will be sotred in the MySQL database that you can
* access historical data as your used to.
* Additional acknowledgement and downtime data will be save in the database
* for reporting und stuff like this as well.
*
**********************************************************************************/

class MemcachedTask extends AppShell{
	public $Config = [];
	public $Memcached = null;
	
	public function init(){
		Configure::load('Statusengine');
		$this->Config = Configure::read('memcached');
		$this->Memcached = new Memcached();
		return $this->Memcached->addServer($this->Config['server'], $this->Config['port']);
	}
	
	public function deleteAll(){
		return $this->Memcached->deleteMulti($this->Memcached->getAllKeys());
	}
	
	public function setHoststatus($payload){
		$key = 'hs_'.md5($payload->hoststatus->name);
		
		$data = [
			'name' => $payload->hoststatus->name,
			'status_update_time' => date('Y-m-d H:i:s', $payload->timestamp),
			'plugin_output' => $payload->hoststatus->plugin_output,
			'long_plugin_output' => $payload->hoststatus->long_plugin_output,
			'event_handler' => $payload->hoststatus->event_handler,
			'perf_data' => $payload->hoststatus->perf_data,
			'check_command' => $payload->hoststatus->check_command,
			'check_period' => $payload->hoststatus->check_period,
			'current_state' => $payload->hoststatus->current_state,
			'has_been_checked' => $payload->hoststatus->has_been_checked,
			'should_be_scheduled' => $payload->hoststatus->should_be_scheduled,
			'current_attempt' => $payload->hoststatus->current_attempt,
			'max_attempts' => $payload->hoststatus->max_attempts,
			'last_check' => date('Y-m-d H:i:s', $payload->hoststatus->last_check),
			'next_check' => date('Y-m-d H:i:s', $payload->hoststatus->next_check),
			'check_type' => $payload->hoststatus->check_type,
			'last_state_change' => $payload->hoststatus->last_state_change,
			'last_hard_state_change' => date('Y-m-d H:i:s', $payload->hoststatus->last_hard_state_change),
			'last_hard_state' => date('Y-m-d H:i:s', $payload->hoststatus->last_hard_state),
			'last_time_up' => date('Y-m-d H:i:s', $payload->hoststatus->last_time_up),
			'last_time_down' => date('Y-m-d H:i:s', $payload->hoststatus->last_time_down),
			'last_time_unreachable' => date('Y-m-d H:i:s', $payload->hoststatus->last_time_unreachable),
			'state_type' => $payload->hoststatus->state_type,
			'last_notification' => date('Y-m-d H:i:s', $payload->hoststatus->last_notification),
			'next_notification' => date('Y-m-d H:i:s', $payload->hoststatus->next_notification),
			'no_more_notifications' => $payload->hoststatus->no_more_notifications,
			'notifications_enabled' => $payload->hoststatus->notifications_enabled,
			'problem_has_been_acknowledged' => $payload->hoststatus->problem_has_been_acknowledged,
			'acknowledgement_type' => $payload->hoststatus->acknowledgement_type,
			'current_notification_number' => $payload->hoststatus->current_notification_number,
			'accept_passive_checks' => $payload->hoststatus->accept_passive_checks,
			'event_handler_enabled' => $payload->hoststatus->event_handler_enabled,
			'checks_enabled' => $payload->hoststatus->checks_enabled,
			'flap_detection_enabled' => $payload->hoststatus->flap_detection_enabled,
			'is_flapping' => $payload->hoststatus->is_flapping,
			'percent_state_change' => $payload->hoststatus->percent_state_change,
			'latency' => $payload->hoststatus->latency,
			'execution_time' => $payload->hoststatus->execution_time,
			'scheduled_downtime_depth' => $payload->hoststatus->scheduled_downtime_depth,
			'process_performance_data' => $payload->hoststatus->process_performance_data,
			'obsess' => $payload->hoststatus->obsess,
			'modified_attributes' => $payload->hoststatus->modified_attributes,
			'check_interval' => $payload->hoststatus->check_interval,
			'retry_interval' => $payload->hoststatus->retry_interval
		];
		
		//Check if there is a old value in the cache
		$oldData = $this->Memcached->get($key);
		if($oldData){
			return $this->Memcached->set($key, array_merge($oldData, $data));
		}
		
		return $this->Memcached->set($key, $data);
	}
	
	public function setServicestatus($payload){
		$key = 'ss_'.md5($payload->servicestatus->host_name.$payload->servicestatus->description);

		$data = [
			'host_name' => $payload->servicestatus->host_name,
			'description' => $payload->servicestatus->description,
			'status_update_time' => date('Y-m-d H:i:s', $payload->timestamp),
			'plugin_output' => $payload->servicestatus->plugin_output,
			'long_plugin_output' => $payload->servicestatus->long_plugin_output,
			'event_handler' => $payload->servicestatus->event_handler,
			'perf_data' => $payload->servicestatus->perf_data,
			'check_command' => $payload->servicestatus->check_command,
			'check_period' => $payload->servicestatus->check_period,
			'current_state' => $payload->servicestatus->current_state,
			'has_been_checked' => $payload->servicestatus->has_been_checked,
			'should_be_scheduled' => $payload->servicestatus->should_be_scheduled,
			'current_attempt' => $payload->servicestatus->current_attempt,
			'max_attempts' => $payload->servicestatus->max_attempts,
			'last_check' => date('Y-m-d H:i:s', $payload->servicestatus->last_check),
			'next_check' => date('Y-m-d H:i:s', $payload->servicestatus->next_check),
			'check_type' => $payload->servicestatus->check_type,
			'last_state_change' => date('Y-m-d H:i:s', $payload->servicestatus->last_state_change),
			'last_hard_state_change' => date('Y-m-d H:i:s', $payload->servicestatus->last_hard_state_change),
			'last_hard_state' => $payload->servicestatus->last_hard_state,
			'last_time_ok' => date('Y-m-d H:i:s', $payload->servicestatus->last_time_ok),
			'last_time_warning' => date('Y-m-d H:i:s', $payload->servicestatus->last_time_warning),
			'last_time_critical' => date('Y-m-d H:i:s', $payload->servicestatus->last_time_critical),
			'last_time_unknown' => date('Y-m-d H:i:s', $payload->servicestatus->last_time_unknown),
			'state_type' => $payload->servicestatus->state_type,
			'last_notification' => date('Y-m-d H:i:s', $payload->servicestatus->last_notification),
			'next_notification' => date('Y-m-d H:i:s', $payload->servicestatus->next_notification),
			'no_more_notifications' => $payload->servicestatus->no_more_notifications,
			'notifications_enabled' => $payload->servicestatus->notifications_enabled,
			'problem_has_been_acknowledged' => $payload->servicestatus->problem_has_been_acknowledged,
			'acknowledgement_type' => $payload->servicestatus->acknowledgement_type,
			'current_notification_number' => $payload->servicestatus->current_notification_number,
			'accept_passive_checks' => $payload->servicestatus->accept_passive_checks,
			'event_handler_enabled' => $payload->servicestatus->event_handler_enabled,
			'checks_enabled' => $payload->servicestatus->checks_enabled,
			'flap_detection_enabled' => $payload->servicestatus->flap_detection_enabled,
			'is_flapping' => $payload->servicestatus->is_flapping,
			'percent_state_change' => $payload->servicestatus->percent_state_change,
			'latency' => $payload->servicestatus->latency,
			'execution_time' => $payload->servicestatus->execution_time,
			'scheduled_downtime_depth' => $payload->servicestatus->scheduled_downtime_depth,
			'process_performance_data' => $payload->servicestatus->process_performance_data,
			'obsess' => $payload->servicestatus->obsess,
			'modified_attributes' => $payload->servicestatus->modified_attributes,
			'check_interval' => $payload->servicestatus->check_interval,
			'retry_interval' => $payload->servicestatus->retry_interval
		];
		
		//Check if there is a old value in the cache
		$oldData = $this->Memcached->get($key);
		if($oldData){
			return $this->Memcached->set($key, array_merge($oldData, $data));
		}
		
		return $this->Memcached->set($key, $data);
	}
	
	public function setAcknowledgement($payload){
		$key = 'ack_'.md5($payload->acknowledgement->host_name.$payload->acknowledgement->service_description);
		$data = [
			'host_name' => $payload->acknowledgement->host_name,
			'service_description' => $payload->acknowledgement->service_description,
			'entry_time' => date('Y-m-d H:i:s', $payload->timestamp),
			'entry_time_usec' => $payload->timestamp,
			'author_name' => $payload->acknowledgement->author_name,
			'comment_data' => $payload->acknowledgement->comment_data,
			'acknowledgement_type' => $payload->acknowledgement->acknowledgement_type,
			'state' => $payload->acknowledgement->state,
			'is_sticky' => $payload->acknowledgement->is_sticky,
			'persistent_comment' => $payload->acknowledgement->persistent_comment,
			'notify_contacts' => $payload->acknowledgement->notify_contacts,
		];
		return $this->Memcached->set($key, $data);
	}
	
}