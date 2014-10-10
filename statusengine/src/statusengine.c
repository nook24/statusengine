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
* What the heck is this?
* Statusengine is a very basic event broker that writes everything to a queueing engine.
* At the moment it only supports the "Gearman job server"
* Every piece of data Statusengine receives from Nagios, will be json encoded and passed
* to the queueing engine
*
* And?
* The php part of Statusengine will writ the data to a MySQL database, or you can use the
* data directly out of the queueing engine and processing them by a own script.
*
* Getting started on Ubuntu 14.04 LTS:
* apt-get install gearman-job-server libgearman-dev gearman-tools uuid-dev php5-gearman php5 php5-cli php5-dev libjson-c-dev manpages-dev build-essential
*
* Compile with the following command:
* LANG=C gcc -shared -o statusengine.o -fPIC  -Wall -Werror statusengine.c -luuid -levent -lgearman -ljson-c
*
* Load the broker in your nagios.cfg
* broker_module=/opt/statusengine/bin/statusengine.o
*
* Not implemented callbacks (becasue no one will ever use this) If you need one of those, contact me
* NEBCALLBACK_ADAPTIVE_CONTACT_DATA
* NEBCALLBACK_ADAPTIVE_PROGRAM_DATA
* NEBCALLBACK_ADAPTIVE_HOST_DATA
* NEBCALLBACK_ADAPTIVE_SERVICE_DATA
* NEBCALLBACK_AGGREGATED_STATUS_DATA
* NEBCALLBACK_RETENTION_DATA
* NEBCALLBACK_TIMED_EVENT_DATA
* 
*
* Have fun :-)
*
**********************************************************************************/

//Load default event broker stuff
#include "../include/nebmodules.h"
#include "../include/nebcallbacks.h"
#include "../include/nebstructs.h"
#include "../include/broker.h"
#include "../include/config.h"
#include "../include/common.h"
#include "../include/nagios.h"
#include "../include/downtime.h"
#include "../include/comments.h"
#include "../include/macros.h"

//Load external libs
#include <libgearman/gearman.h>
#include <json-c/json.h>

// specify event broker API version (required)
NEB_API_VERSION(CURRENT_NEB_API_VERSION);


/**** NAGIOS VARIABLES ****/
extern command *command_list;
extern timeperiod *timeperiod_list;
extern contact *contact_list;
extern contactgroup *contactgroup_list;
extern host *host_list;
extern hostgroup *hostgroup_list;
extern service *service_list;
extern servicegroup *servicegroup_list;
extern hostescalation *hostescalation_list;
extern serviceescalation *serviceescalation_list;
extern hostdependency *hostdependency_list;
extern servicedependency *servicedependency_list;


extern char *config_file;
extern sched_info scheduling_info;
extern char *global_host_event_handler;
extern char *global_service_event_handler;



gearman_return_t ret; //remove me!!!
gearman_client_st client;

void *statusengine_module_handle = NULL;

int statusengine_handle_data(int, void *);
void dump_object_data();


//Broker initialize function
int nebmodule_init(int flags, char *args, nebmodule *handle){

	//Save handle
	statusengine_module_handle = handle;

	//I guess nagios don't use this?
	neb_set_module_info(statusengine_module_handle, NEBMODULE_MODINFO_TITLE,   "Statusengine - the missing event broker");
	neb_set_module_info(statusengine_module_handle, NEBMODULE_MODINFO_AUTHOR,  "Daniel Ziegler");
	neb_set_module_info(statusengine_module_handle, NEBMODULE_MODINFO_TITLE,   "Copyright (c) 2014 - present Daniel Ziegler");
	neb_set_module_info(statusengine_module_handle, NEBMODULE_MODINFO_VERSION, "1.0.0");
	neb_set_module_info(statusengine_module_handle, NEBMODULE_MODINFO_LICENSE, "GPL v2");
	neb_set_module_info(statusengine_module_handle, NEBMODULE_MODINFO_DESC,    "A powerful and flexible event broker");

	//Welcome messages
	write_to_all_logs("Statusengine - the missing event broker",                                               NSLOG_INFO_MESSAGE);
	write_to_all_logs("[Statusengine] Copyright (c) 2014 - present Daniel Ziegler <daniel@statusengine.org>",  NSLOG_INFO_MESSAGE);
	write_to_all_logs("[Statusengine] Please visit http://www.statusengine.org for more information",          NSLOG_INFO_MESSAGE);
	write_to_all_logs("[Statusengine] Contribute to Statusenigne at: https://github.com/nook24/statusengine",  NSLOG_INFO_MESSAGE);
	write_to_all_logs("[Statusengine] Thanks for using Statusengine :-)",                                      NSLOG_INFO_MESSAGE);

	//Register callbacks
	write_to_all_logs("[Statusengine] Register callbacks", NSLOG_INFO_MESSAGE);
	neb_register_callback(NEBCALLBACK_HOST_STATUS_DATA,                 statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_SERVICE_STATUS_DATA,              statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_PROCESS_DATA,                     statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_SERVICE_CHECK_DATA,               statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_HOST_CHECK_DATA,                  statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_STATE_CHANGE_DATA,                statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_LOG_DATA,                         statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_SYSTEM_COMMAND_DATA,              statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_COMMENT_DATA,                     statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_EXTERNAL_COMMAND_DATA,            statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_ACKNOWLEDGEMENT_DATA,             statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_FLAPPING_DATA,                    statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_DOWNTIME_DATA,                    statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_NOTIFICATION_DATA,                statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_PROGRAM_STATUS_DATA,              statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_CONTACT_STATUS_DATA,              statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_CONTACT_NOTIFICATION_DATA,        statusengine_module_handle, 0, statusengine_handle_data);
	neb_register_callback(NEBCALLBACK_CONTACT_NOTIFICATION_METHOD_DATA, statusengine_module_handle, 0, statusengine_handle_data);
	

	//Create gearman client
	if (gearman_client_create(&client) == NULL){
		write_to_all_logs("[Statusengine] Memory allocation failure on client creation\n", NSLOG_INFO_MESSAGE);
	}

	ret= gearman_client_add_server(&client, "127.0.0.1", 4730);
	if (ret != GEARMAN_SUCCESS){
		write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);
	}

	return 0;
}

//Broker deinitialize function
int nebmodule_deinit(int flags, int reason){

	// Deregister all callbacks
	write_to_all_logs("[Statusengine] Deregister callbacks", NSLOG_INFO_MESSAGE);
	neb_deregister_callback(NEBCALLBACK_HOST_STATUS_DATA,                 statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_SERVICE_STATUS_DATA,              statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_PROCESS_DATA,                     statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_SERVICE_CHECK_DATA,               statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_HOST_CHECK_DATA,                  statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_STATE_CHANGE_DATA,                statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_LOG_DATA,                         statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_SYSTEM_COMMAND_DATA,              statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_COMMENT_DATA,                     statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_EXTERNAL_COMMAND_DATA,            statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_ACKNOWLEDGEMENT_DATA,             statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_FLAPPING_DATA,                    statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_DOWNTIME_DATA,                    statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_NOTIFICATION_DATA,                statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_PROGRAM_STATUS_DATA,              statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_CONTACT_STATUS_DATA,              statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_CONTACT_NOTIFICATION_DATA,        statusengine_handle_data);
	neb_deregister_callback(NEBCALLBACK_CONTACT_NOTIFICATION_METHOD_DATA, statusengine_handle_data);

	write_to_all_logs("[Statusengine] We are done here", NSLOG_INFO_MESSAGE);
	write_to_all_logs("[Statusengine] Bye", NSLOG_INFO_MESSAGE);

	//Delete gearman client
	gearman_client_free(&client);

	return 0;
}


#define HOSTFIELD_STRING(FIELD) \
	json_object_object_add(host_object, #FIELD, (nag_hoststatus->FIELD != NULL ? json_object_new_string(nag_hoststatus->FIELD) : NULL))

#define HOSTFIELD_INT(FIELD) \
	json_object_object_add(host_object, #FIELD, json_object_new_int64(nag_hoststatus->FIELD))

#define HOSTFIELD_DOUBLE(FIELD) \
	json_object_object_add(host_object, #FIELD, json_object_new_double(nag_hoststatus->FIELD))

#define SERVICEFIELD_STRING(FIELD) \
	json_object_object_add(service_object, #FIELD, (nag_servicestatus->FIELD != NULL ? json_object_new_string(nag_servicestatus->FIELD) : NULL))

#define SERVICEFIELD_INT(FIELD) \
	json_object_object_add(service_object, #FIELD, json_object_new_int64(nag_servicestatus->FIELD))

#define SERVICEFIELD_DOUBLE(FIELD) \
	json_object_object_add(service_object, #FIELD, json_object_new_double(nag_servicestatus->FIELD))

#define SERVICECHECKFIELD_STRING(FIELD) \
	json_object_object_add(servicecheck_object, #FIELD, (nag_servicecheck->FIELD != NULL ? json_object_new_string(nag_servicecheck->FIELD) : NULL))

#define SERVICECHECKFIELD_INT(FIELD) \
	json_object_object_add(servicecheck_object, #FIELD, json_object_new_int64(nag_servicecheck->FIELD))

#define SERVICECHECKFIELD_DOUBLE(FIELD) \
	json_object_object_add(servicecheck_object, #FIELD, json_object_new_double(nag_servicecheck->FIELD))

#define HOSTCHECKFIELD_STRING(FIELD) \
	json_object_object_add(hostcheck_object, #FIELD, (nag_hostcheck->FIELD != NULL ? json_object_new_string(nag_hostcheck->FIELD) : NULL))

#define HOSTCHECKFIELD_INT(FIELD) \
	json_object_object_add(hostcheck_object, #FIELD, json_object_new_int64(nag_hostcheck->FIELD))

#define HOSTCHECKFIELD_DOUBLE(FIELD) \
	json_object_object_add(hostcheck_object, #FIELD, json_object_new_double(nag_hostcheck->FIELD))
		
#define STATECHANGE_STRING(FIELD) \
	json_object_object_add(statechange_object, #FIELD, (statechange->FIELD != NULL ? json_object_new_string(statechange->FIELD) : NULL))

#define STATECHANGE_INT(FIELD) \
	json_object_object_add(statechange_object, #FIELD, json_object_new_int64(statechange->FIELD))

//Handle callback data
int statusengine_handle_data(int event_type, void *data){
	nebstruct_host_status_data                 *hoststatusdata    = NULL;
	nebstruct_service_status_data              *servicestatusdata = NULL;
	nebstruct_process_data                     *programmdata      = NULL;
	nebstruct_service_check_data               *servicecheck      = NULL;
	char *raw_command                                             = NULL;
	nebstruct_host_check_data                  *hostcheck         = NULL;
	nebstruct_statechange_data                 *statechange       = NULL;
	host                                       *tmp_host          = NULL;
	service                                    *tmp_service       = NULL;
	int                                        last_state         = -1;
	int                                        last_hard_state    = -1;
	nebstruct_log_data                         *logentry          = NULL;
	nebstruct_system_command_data              *systemcommand     = NULL;
	nebstruct_comment_data                     *_comment          = NULL;
	nebstruct_external_command_data            *extcommand        = NULL;
	nebstruct_acknowledgement_data             *acknowledgement   = NULL;
	nebstruct_flapping_data                    *_flapping         = NULL;
	comment                                    *tmp_comment       = NULL;
	nebstruct_downtime_data                    *_downtime         = NULL;
	nebstruct_notification_data                *notificationdata  = NULL;
	nebstruct_program_status_data              *procstats         = NULL;
	nebstruct_contact_status_data              *contactstatus     = NULL;
	contact                                    *tmp_contact       = NULL;
	nebstruct_contact_notification_data        *cnd               = NULL;
	nebstruct_contact_notification_method_data *cnm        = NULL;
	json_object *my_object;

	switch(event_type){

		case NEBCALLBACK_PROCESS_DATA:

			programmdata=(nebstruct_process_data *)data;
			if(programmdata == NULL){
				return 0;
			}

			//Core process was started, so we need to dump every object
			if(programmdata->type == NEBTYPE_PROCESS_START){
				dump_object_data();
			}
			
			if((programmdata = (nebstruct_process_data *)data)){
				my_object = json_object_new_object();
				json_object_object_add(my_object, "type",      json_object_new_int(programmdata->type));
				json_object_object_add(my_object, "flags",     json_object_new_int(programmdata->flags));
				json_object_object_add(my_object, "attr",      json_object_new_int(programmdata->attr));
				json_object_object_add(my_object, "timestamp", json_object_new_int(programmdata->timestamp.tv_sec));
				json_object *processdata_object = json_object_new_object();
				json_object_object_add(processdata_object, "programmname",      json_object_new_string("Nagios"));
				json_object_object_add(processdata_object, "modification_data", json_object_new_string(get_program_modification_date()));
				json_object_object_add(processdata_object, "programmversion",   json_object_new_string(get_program_version()));
				json_object_object_add(processdata_object, "pid",               json_object_new_int64(getpid()));
		
				json_object_object_add(my_object, "processdata", processdata_object);
				const char* json_string = json_object_to_json_string(my_object);
				ret= gearman_client_do_background(&client, "processdata", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
				if (ret != GEARMAN_SUCCESS)
					write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

				json_object_put(my_object);
			}
			
			break;


			case NEBCALLBACK_HOST_STATUS_DATA:
				if((hoststatusdata = (nebstruct_host_status_data *)data)){
					if(hoststatusdata == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(hoststatusdata->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(hoststatusdata->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(hoststatusdata->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(hoststatusdata->timestamp.tv_sec));

					json_object *host_object = json_object_new_object();
					host *nag_hoststatus = (host *)hoststatusdata->object_ptr;

					if(nag_hoststatus == NULL){
						return 0;
					}

					HOSTFIELD_STRING(name);
					HOSTFIELD_STRING(plugin_output);
					HOSTFIELD_STRING(long_plugin_output);
					HOSTFIELD_STRING(event_handler);
					HOSTFIELD_STRING(perf_data);
					HOSTFIELD_STRING(check_command);
					HOSTFIELD_STRING(check_period);
					HOSTFIELD_INT(current_state);
					HOSTFIELD_INT(has_been_checked);
					HOSTFIELD_INT(should_be_scheduled);
					HOSTFIELD_INT(current_attempt);
					HOSTFIELD_INT(max_attempts);
					HOSTFIELD_INT(last_check);
					HOSTFIELD_INT(next_check);
					HOSTFIELD_INT(check_type);
					HOSTFIELD_INT(last_state_change);
					HOSTFIELD_INT(last_hard_state_change);
					HOSTFIELD_INT(last_hard_state);
					HOSTFIELD_INT(last_time_up);
					HOSTFIELD_INT(last_time_down);
					HOSTFIELD_INT(last_time_unreachable);
					HOSTFIELD_INT(state_type);
					HOSTFIELD_INT(last_notification);
					HOSTFIELD_INT(next_notification);
					HOSTFIELD_INT(no_more_notifications);
					HOSTFIELD_INT(notifications_enabled);
					HOSTFIELD_INT(problem_has_been_acknowledged);
					HOSTFIELD_INT(acknowledgement_type);
					HOSTFIELD_INT(current_notification_number);
					HOSTFIELD_INT(accept_passive_checks);
					HOSTFIELD_INT(event_handler_enabled);
					HOSTFIELD_INT(checks_enabled);
					HOSTFIELD_INT(flap_detection_enabled);
					HOSTFIELD_INT(is_flapping);
					HOSTFIELD_DOUBLE(percent_state_change);
					HOSTFIELD_DOUBLE(latency);
					HOSTFIELD_DOUBLE(execution_time);
					HOSTFIELD_INT(scheduled_downtime_depth);
					HOSTFIELD_INT(process_performance_data);
					HOSTFIELD_INT(obsess);
					HOSTFIELD_INT(modified_attributes);
					HOSTFIELD_DOUBLE(check_interval);
					HOSTFIELD_DOUBLE(retry_interval);

					json_object_object_add(my_object, "hoststatus", host_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "hoststatus", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

					json_object_put(my_object);

				}
				break;

			case NEBCALLBACK_SERVICE_STATUS_DATA:
				if((servicestatusdata = (nebstruct_service_status_data *)data)){
					if(servicestatusdata == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(servicestatusdata->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(servicestatusdata->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(servicestatusdata->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(servicestatusdata->timestamp.tv_sec));

					json_object *service_object = json_object_new_object();
					service *nag_servicestatus = (service *)servicestatusdata->object_ptr;

					if(nag_servicestatus == NULL){
						return 0;
					}

					SERVICEFIELD_STRING(host_name);
					SERVICEFIELD_STRING(description);
					SERVICEFIELD_STRING(plugin_output);
					SERVICEFIELD_STRING(long_plugin_output);
					SERVICEFIELD_STRING(event_handler);
					SERVICEFIELD_STRING(perf_data);
					SERVICEFIELD_STRING(check_command);
					SERVICEFIELD_STRING(check_period);
					SERVICEFIELD_INT(current_state);
					SERVICEFIELD_INT(has_been_checked);
					SERVICEFIELD_INT(should_be_scheduled);
					SERVICEFIELD_INT(current_attempt);
					SERVICEFIELD_INT(max_attempts);
					SERVICEFIELD_INT(last_check);
					SERVICEFIELD_INT(next_check);
					SERVICEFIELD_INT(check_type);
					SERVICEFIELD_INT(last_state_change);
					SERVICEFIELD_INT(last_hard_state_change);
					SERVICEFIELD_INT(last_hard_state);
					SERVICEFIELD_INT(last_time_ok);
					SERVICEFIELD_INT(last_time_warning);
					SERVICEFIELD_INT(last_time_critical);
					SERVICEFIELD_INT(last_time_unknown);
					SERVICEFIELD_INT(state_type);
					SERVICEFIELD_INT(last_notification);
					SERVICEFIELD_INT(next_notification);
					SERVICEFIELD_INT(no_more_notifications);
					SERVICEFIELD_INT(notifications_enabled);
					SERVICEFIELD_INT(problem_has_been_acknowledged);
					SERVICEFIELD_INT(acknowledgement_type);
					SERVICEFIELD_INT(current_notification_number);
					SERVICEFIELD_INT(accept_passive_checks);
					SERVICEFIELD_INT(event_handler_enabled);
					SERVICEFIELD_INT(checks_enabled);
					SERVICEFIELD_INT(flap_detection_enabled);
					SERVICEFIELD_INT(is_flapping);
					SERVICEFIELD_DOUBLE(percent_state_change);
					SERVICEFIELD_DOUBLE(latency);
					SERVICEFIELD_DOUBLE(execution_time);
					SERVICEFIELD_INT(scheduled_downtime_depth);
					SERVICEFIELD_INT(process_performance_data);
					SERVICEFIELD_INT(obsess);
					SERVICEFIELD_INT(modified_attributes);
					SERVICEFIELD_DOUBLE(check_interval);
					SERVICEFIELD_DOUBLE(retry_interval);

					json_object_object_add(my_object, "servicestatus", service_object);

					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "servicestatus", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);
				
					json_object_put(my_object);

				}
				break;

			case NEBCALLBACK_SERVICE_CHECK_DATA:
				if((servicecheck = (nebstruct_service_check_data *)data)){
					if(servicecheck == NULL){
						return 0;
					}

					// We drop some data we dont need, but i have no idea what we drop ?!
					if(servicecheck->type!=701){
						break;
					}

					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(servicecheck->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(servicecheck->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(servicecheck->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(servicecheck->timestamp.tv_sec));

					json_object *servicecheck_object = json_object_new_object();
					nebstruct_service_check_data *nag_servicecheck = servicecheck;
					service *nag_service = (service *)nag_servicecheck->object_ptr;

					SERVICECHECKFIELD_STRING(host_name);
					SERVICECHECKFIELD_STRING(service_description);

					get_raw_command_line(nag_service->check_command_ptr,nag_service->check_command,&raw_command,0);
					json_object_object_add(servicecheck_object, "command_line", (raw_command != NULL ? json_object_new_string(raw_command) : NULL));
					json_object_object_add(servicecheck_object, "command_name", (nag_service->check_command != NULL ? json_object_new_string(nag_service->check_command) : NULL));

					SERVICECHECKFIELD_STRING(output);
					SERVICECHECKFIELD_STRING(long_output);
					SERVICECHECKFIELD_STRING(perf_data);
					SERVICECHECKFIELD_INT(check_type);
					SERVICECHECKFIELD_INT(current_attempt);
					SERVICECHECKFIELD_INT(max_attempts);
					SERVICECHECKFIELD_INT(state_type);
					SERVICECHECKFIELD_INT(state);
					SERVICECHECKFIELD_INT(timeout);
					json_object_object_add(servicecheck_object, "start_time", json_object_new_int64(nag_servicecheck->start_time.tv_sec));
					json_object_object_add(servicecheck_object, "end_time", json_object_new_int64(nag_servicecheck->end_time.tv_sec));
					SERVICECHECKFIELD_INT(early_timeout);
					SERVICECHECKFIELD_DOUBLE(execution_time);
					SERVICECHECKFIELD_DOUBLE(latency);
					SERVICECHECKFIELD_INT(return_code);


					json_object_object_add(my_object, "servicecheck", servicecheck_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "servicechecks", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);
					
					json_object_put(my_object);

				}
				break;

			case NEBCALLBACK_HOST_CHECK_DATA:
				if((hostcheck = (nebstruct_host_check_data *)data)){
					if(hostcheck == NULL){
						return 0;
					}

					// We drop some data we dont need, but i have no idea what we drop ?!
					if(hostcheck->type!=801){
						break;
					}

					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(hostcheck->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(hostcheck->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(hostcheck->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(hostcheck->timestamp.tv_sec));

					json_object *hostcheck_object = json_object_new_object();
					nebstruct_host_check_data *nag_hostcheck = hostcheck;
					host *nag_host = (host *)nag_hostcheck->object_ptr;

					HOSTCHECKFIELD_STRING(host_name);

					get_raw_command_line(nag_host->check_command_ptr,nag_host->check_command,&raw_command,0);
					json_object_object_add(hostcheck_object, "command_line", (raw_command != NULL ? json_object_new_string(raw_command) : NULL));
					json_object_object_add(hostcheck_object, "command_name", (nag_host->check_command != NULL ? json_object_new_string(nag_host->check_command) : NULL));

					HOSTCHECKFIELD_STRING(output);
					HOSTCHECKFIELD_STRING(long_output);
					HOSTCHECKFIELD_STRING(perf_data);
					HOSTCHECKFIELD_INT(check_type);
					HOSTCHECKFIELD_INT(current_attempt);
					HOSTCHECKFIELD_INT(max_attempts);
					HOSTCHECKFIELD_INT(state_type);
					HOSTCHECKFIELD_INT(state);
					HOSTCHECKFIELD_INT(timeout);
					json_object_object_add(hostcheck_object, "start_time", json_object_new_int64(nag_hostcheck->start_time.tv_sec));
					json_object_object_add(hostcheck_object, "end_time", json_object_new_int64(nag_hostcheck->end_time.tv_sec));
					HOSTCHECKFIELD_INT(early_timeout);
					HOSTCHECKFIELD_DOUBLE(execution_time);
					HOSTCHECKFIELD_DOUBLE(latency);
					HOSTCHECKFIELD_INT(return_code);


					json_object_object_add(my_object, "hostcheck", hostcheck_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "hostchecks", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

					json_object_put(my_object);
				
				}
				break;

			case NEBCALLBACK_STATE_CHANGE_DATA:
				if((statechange = (nebstruct_statechange_data *)data)){
					if(statechange == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(statechange->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(statechange->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(statechange->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(statechange->timestamp.tv_sec));

					json_object *statechange_object = json_object_new_object();

					if(statechange->service_description == NULL){
						//We have a host
						if((tmp_host = (host *)statechange->object_ptr) == NULL){
							//Host is gone?
							return 0;
						}
						last_state = tmp_host->last_state;
						last_hard_state = tmp_host->last_hard_state;
					}else{
						//We have a service
						if((tmp_service = (service *)statechange->object_ptr) == NULL){
							//Service is gone?
							return 0;
						}
						last_state = tmp_service->last_state;
						last_hard_state = tmp_service->last_hard_state;
					}

					STATECHANGE_STRING(host_name);
					STATECHANGE_STRING(service_description);
					STATECHANGE_STRING(output);
					//ther is no long output at the moment for statehistory, or???
					//STATECHANGE_STRING(long_output);
					json_object_object_add(statechange_object, "long_output", (statechange->output != NULL ? json_object_new_string(statechange->output) : NULL));


					STATECHANGE_INT(statechange_type);
					STATECHANGE_INT(state);
					STATECHANGE_INT(state_type);
					STATECHANGE_INT(current_attempt);
					STATECHANGE_INT(max_attempts);
					json_object_object_add(statechange_object, "last_state", json_object_new_int64(last_state));
					json_object_object_add(statechange_object, "last_hard_state", json_object_new_int64(last_hard_state));

					json_object_object_add(my_object, "statechange", statechange_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "statechanges", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

					json_object_put(my_object);
				}
				break;

			case NEBCALLBACK_LOG_DATA:
				if((logentry = (nebstruct_log_data *)data)){
					if(logentry == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(logentry->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(logentry->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(logentry->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(logentry->timestamp.tv_sec));

					json_object *logentry_object = json_object_new_object();

					json_object_object_add(logentry_object, "entry_time", json_object_new_int64(logentry->entry_time));
					json_object_object_add(logentry_object, "data_type", json_object_new_int64(logentry->data_type));
					json_object_object_add(logentry_object, "data", (logentry->data != NULL ? json_object_new_string(logentry->data) : NULL));

					json_object_object_add(my_object, "logentry", logentry_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "logentries", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

					json_object_put(my_object);
				}
				break;

			case NEBCALLBACK_SYSTEM_COMMAND_DATA:
				if((systemcommand = (nebstruct_system_command_data *)data)){
					if(systemcommand == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(systemcommand->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(systemcommand->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(systemcommand->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(systemcommand->timestamp.tv_sec));

					json_object *systemcommand_object = json_object_new_object();

					json_object_object_add(systemcommand_object, "command_line", (systemcommand->command_line != NULL ? json_object_new_string(systemcommand->command_line) : NULL));
					json_object_object_add(systemcommand_object, "output",       (systemcommand->output       != NULL ? json_object_new_string(systemcommand->output)       : NULL));
					//I guess this is long output one day...
					json_object_object_add(systemcommand_object, "long_output",  (systemcommand->output       != NULL ? json_object_new_string(systemcommand->output)       : NULL));

					json_object_object_add(systemcommand_object, "start_time",     json_object_new_int64(systemcommand->start_time.tv_sec));
					json_object_object_add(systemcommand_object, "end_time",       json_object_new_int64(systemcommand->end_time.tv_sec));
					json_object_object_add(systemcommand_object, "timeout",        json_object_new_int64(systemcommand->timeout));
					json_object_object_add(systemcommand_object, "early_timeout",  json_object_new_int64(systemcommand->early_timeout));
					json_object_object_add(systemcommand_object, "return_code",    json_object_new_int64(systemcommand->return_code));

					json_object_object_add(systemcommand_object, "execution_time", json_object_new_double(systemcommand->execution_time));

					json_object_object_add(my_object, "systemcommand", systemcommand_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "systemcommands", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

					json_object_put(my_object);
				}
				break;


			case NEBCALLBACK_COMMENT_DATA:
				if((_comment = (nebstruct_comment_data *)data)){
					if(_comment == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(_comment->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(_comment->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(_comment->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(_comment->timestamp.tv_sec));

					json_object *comment_object = json_object_new_object();
					json_object_object_add(comment_object, "host_name",           (_comment->host_name           != NULL ? json_object_new_string(_comment->host_name) : NULL));
					json_object_object_add(comment_object, "service_description", (_comment->service_description != NULL ? json_object_new_string(_comment->service_description) : NULL));
					json_object_object_add(comment_object, "author_name",         (_comment->author_name         != NULL ? json_object_new_string(_comment->author_name) : NULL));
					json_object_object_add(comment_object, "comment_data",        (_comment->comment_data        != NULL ? json_object_new_string(_comment->comment_data) : NULL));

					json_object_object_add(comment_object, "comment_type", json_object_new_int64(_comment->comment_type));
					json_object_object_add(comment_object, "entry_time",   json_object_new_int64(_comment->entry_time));
					json_object_object_add(comment_object, "persistent",   json_object_new_int64(_comment->persistent));
					json_object_object_add(comment_object, "source",       json_object_new_int64(_comment->source));
					json_object_object_add(comment_object, "entry_type",   json_object_new_int64(_comment->entry_type));
					json_object_object_add(comment_object, "expires",      json_object_new_int64(_comment->expires));
					json_object_object_add(comment_object, "expire_time",  json_object_new_int64(_comment->expire_time));
					json_object_object_add(comment_object, "comment_id",   json_object_new_int64(_comment->comment_id));

					json_object_object_add(my_object, "comment", comment_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "comments", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

					json_object_put(my_object);
				}
				break;

			case NEBCALLBACK_EXTERNAL_COMMAND_DATA:
				if((extcommand = (nebstruct_external_command_data *)data)){
					if(extcommand == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(extcommand->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(extcommand->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(extcommand->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(extcommand->timestamp.tv_sec));

					json_object *extcommand_object = json_object_new_object();
					json_object_object_add(extcommand_object, "command_string", (extcommand->command_string != NULL ? json_object_new_string(extcommand->command_string) : NULL));
					json_object_object_add(extcommand_object, "command_args",   (extcommand->command_args   != NULL ? json_object_new_string(extcommand->command_args) : NULL));
					json_object_object_add(extcommand_object, "command_type",   json_object_new_int64(extcommand->command_type));
					json_object_object_add(extcommand_object, "entry_time",     json_object_new_int64(extcommand->entry_time));

					json_object_object_add(my_object, "externalcommand", extcommand_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "externalcommands", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);
			
					json_object_put(my_object);
				}
				break;

			case NEBCALLBACK_ACKNOWLEDGEMENT_DATA:
				if((acknowledgement = (nebstruct_acknowledgement_data *)data)){
					if(acknowledgement == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(acknowledgement->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(acknowledgement->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(acknowledgement->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(acknowledgement->timestamp.tv_sec));

					json_object *acknowledgement_object = json_object_new_object();
					json_object_object_add(acknowledgement_object, "host_name",           (acknowledgement->host_name           != NULL ? json_object_new_string(acknowledgement->host_name) : NULL));
					json_object_object_add(acknowledgement_object, "service_description", (acknowledgement->service_description != NULL ? json_object_new_string(acknowledgement->service_description) : NULL));
					json_object_object_add(acknowledgement_object, "author_name",         (acknowledgement->author_name         != NULL ? json_object_new_string(acknowledgement->author_name) : NULL));
					json_object_object_add(acknowledgement_object, "comment_data",        (acknowledgement->comment_data        != NULL ? json_object_new_string(acknowledgement->comment_data) : NULL));

					json_object_object_add(acknowledgement_object, "acknowledgement_type", json_object_new_int64(acknowledgement->acknowledgement_type));
					json_object_object_add(acknowledgement_object, "state",                json_object_new_int64(acknowledgement->state));
					json_object_object_add(acknowledgement_object, "is_sticky",            json_object_new_int64(acknowledgement->is_sticky));
					json_object_object_add(acknowledgement_object, "persistent_comment",   json_object_new_int64(acknowledgement->persistent_comment));
					json_object_object_add(acknowledgement_object, "notify_contacts",      json_object_new_int64(acknowledgement->notify_contacts));

					json_object_object_add(my_object, "acknowledgement", acknowledgement_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "acknowledgements", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

					json_object_put(my_object);
				}
				break;

			case NEBCALLBACK_FLAPPING_DATA:
				if((_flapping = (nebstruct_flapping_data *)data)){
					if(_flapping == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(_flapping->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(_flapping->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(_flapping->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(_flapping->timestamp.tv_sec));

					json_object *flapping_object = json_object_new_object();
					json_object_object_add(flapping_object, "host_name",           (_flapping->host_name           != NULL ? json_object_new_string(_flapping->host_name) : NULL));
					json_object_object_add(flapping_object, "service_description", (_flapping->service_description != NULL ? json_object_new_string(_flapping->service_description) : NULL));
					

					if(_flapping->flapping_type == 0){
						//I'm a host
						tmp_comment = find_host_comment(_flapping->comment_id);
					}else{
						//I'm a service
						tmp_comment = find_service_comment(_flapping->comment_id);
					}
					
					json_object_object_add(flapping_object, "flapping_type",      json_object_new_int64(_flapping->flapping_type));
					json_object_object_add(flapping_object, "comment_id",         json_object_new_int64(_flapping->comment_id));
					
					//May be you can explain me this?
					if(tmp_comment != NULL){
						json_object_object_add(flapping_object, "comment_entry_time", json_object_new_int64(tmp_comment->entry_time));
					}else{
						json_object_object_add(flapping_object, "comment_entry_time", json_object_new_int64(0));
					}

					json_object_object_add(flapping_object, "percent_change", json_object_new_double(_flapping->percent_change));
					json_object_object_add(flapping_object, "high_threshold", json_object_new_double(_flapping->high_threshold));
					json_object_object_add(flapping_object, "low_threshold",  json_object_new_double(_flapping->low_threshold));

					json_object_object_add(my_object, "flapping", flapping_object);
					const char* json_string = json_object_to_json_string(my_object);
					//I'm not very happy with this queue name....
					ret= gearman_client_do_background(&client, "flappings", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

					json_object_put(my_object);
				}
				break;

			case NEBCALLBACK_DOWNTIME_DATA:
				if((_downtime = (nebstruct_downtime_data *)data)){
					if(_downtime == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(_downtime->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(_downtime->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(_downtime->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(_downtime->timestamp.tv_sec));

					json_object *downtime_object = json_object_new_object();
					json_object_object_add(downtime_object, "host_name",           (_downtime->host_name           != NULL ? json_object_new_string(_downtime->host_name) : NULL));
					json_object_object_add(downtime_object, "service_description", (_downtime->service_description != NULL ? json_object_new_string(_downtime->service_description) : NULL));
					json_object_object_add(downtime_object, "author_name",         (_downtime->author_name         != NULL ? json_object_new_string(_downtime->author_name) : NULL));
					json_object_object_add(downtime_object, "comment_data",        (_downtime->comment_data        != NULL ? json_object_new_string(_downtime->comment_data) : NULL));
					json_object_object_add(downtime_object, "host_name",           (_downtime->host_name           != NULL ? json_object_new_string(_downtime->host_name) : NULL));
					

					json_object_object_add(downtime_object, "downtime_type", json_object_new_int64(_downtime->downtime_type));
					json_object_object_add(downtime_object, "entry_time",    json_object_new_int64(_downtime->entry_time));
					json_object_object_add(downtime_object, "start_time",    json_object_new_int64(_downtime->start_time));
					json_object_object_add(downtime_object, "end_time",      json_object_new_int64(_downtime->end_time));
					json_object_object_add(downtime_object, "triggered_by",  json_object_new_int64(_downtime->triggered_by));
					json_object_object_add(downtime_object, "downtime_id",   json_object_new_int64(_downtime->downtime_id));
					json_object_object_add(downtime_object, "fixed",         json_object_new_int64(_downtime->fixed));
					
					json_object_object_add(downtime_object, "duration",      json_object_new_double(_downtime->duration));
	

					json_object_object_add(my_object, "downtime", downtime_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "downtimes", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

					json_object_put(my_object);
				}
				break;
				
			case NEBCALLBACK_NOTIFICATION_DATA:
				if((notificationdata = (nebstruct_notification_data *)data)){
					if(notificationdata == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(notificationdata->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(notificationdata->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(notificationdata->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(notificationdata->timestamp.tv_sec));

					json_object *notification_data_object = json_object_new_object();
					json_object_object_add(notification_data_object, "host_name",           (notificationdata->host_name           != NULL ? json_object_new_string(notificationdata->host_name) : NULL));
					json_object_object_add(notification_data_object, "service_description", (notificationdata->service_description != NULL ? json_object_new_string(notificationdata->service_description) : NULL));
					json_object_object_add(notification_data_object, "output",              (notificationdata->output              != NULL ? json_object_new_string(notificationdata->output) : NULL));
					//May be some day, how knows?
					json_object_object_add(notification_data_object, "long_output",         (notificationdata->output              != NULL ? json_object_new_string(notificationdata->output) : NULL));
					json_object_object_add(notification_data_object, "ack_author",          (notificationdata->ack_author          != NULL ? json_object_new_string(notificationdata->ack_author) : NULL));
					json_object_object_add(notification_data_object, "ack_data",            (notificationdata->ack_data           != NULL ? json_object_new_string(notificationdata->ack_data) : NULL));
					json_object_object_add(notification_data_object, "host_name",           (notificationdata->host_name           != NULL ? json_object_new_string(notificationdata->host_name) : NULL));

					json_object_object_add(notification_data_object, "notification_type", json_object_new_int64(notificationdata->notification_type));
					json_object_object_add(notification_data_object, "start_time",        json_object_new_int64(notificationdata->start_time.tv_sec));
					json_object_object_add(notification_data_object, "end_time",          json_object_new_int64(notificationdata->end_time.tv_sec));
					json_object_object_add(notification_data_object, "reason_type",       json_object_new_int64(notificationdata->reason_type));
					json_object_object_add(notification_data_object, "state",             json_object_new_int64(notificationdata->state));
					json_object_object_add(notification_data_object, "escalated",         json_object_new_int64(notificationdata->escalated));
					json_object_object_add(notification_data_object, "contacts_notified", json_object_new_int64(notificationdata->contacts_notified));

					json_object_object_add(my_object, "notification_data", notification_data_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "notifications", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

					json_object_put(my_object);

				}
				break;

			case NEBCALLBACK_PROGRAM_STATUS_DATA:
				if((procstats = (nebstruct_program_status_data *)data)){
					if(procstats == NULL){
						return 0;
					}
					my_object = json_object_new_object();
					json_object_object_add(my_object, "type",      json_object_new_int(procstats->type));
					json_object_object_add(my_object, "flags",     json_object_new_int(procstats->flags));
					json_object_object_add(my_object, "attr",      json_object_new_int(procstats->attr));
					json_object_object_add(my_object, "timestamp", json_object_new_int(procstats->timestamp.tv_sec));

					json_object *programmstatus_object = json_object_new_object();
					json_object_object_add(programmstatus_object, "global_host_event_handler",    (procstats->global_host_event_handler != NULL ? json_object_new_string(procstats->global_host_event_handler) : NULL));
					json_object_object_add(programmstatus_object, "global_service_event_handler", (procstats->global_host_event_handler != NULL ? json_object_new_string(procstats->global_service_event_handler) : NULL));

					json_object_object_add(programmstatus_object, "program_start",                  json_object_new_int64(procstats->program_start));
					json_object_object_add(programmstatus_object, "pid",                            json_object_new_int64(procstats->pid));
					json_object_object_add(programmstatus_object, "daemon_mode",                    json_object_new_int64(procstats->daemon_mode));
					//I guess this is removed in nagios 4?
					json_object_object_add(programmstatus_object, "last_command_check",             json_object_new_int64(0));
					json_object_object_add(programmstatus_object, "last_log_rotation",              json_object_new_int64(procstats->last_log_rotation));
					json_object_object_add(programmstatus_object, "notifications_enabled",          json_object_new_int64(procstats->notifications_enabled));
					json_object_object_add(programmstatus_object, "active_service_checks_enabled",  json_object_new_int64(procstats->active_service_checks_enabled));
					json_object_object_add(programmstatus_object, "passive_service_checks_enabled", json_object_new_int64(procstats->passive_service_checks_enabled));
					json_object_object_add(programmstatus_object, "active_host_checks_enabled",     json_object_new_int64(procstats->active_host_checks_enabled));
					json_object_object_add(programmstatus_object, "passive_host_checks_enabled",    json_object_new_int64(procstats->passive_host_checks_enabled));
					json_object_object_add(programmstatus_object, "event_handlers_enabled",         json_object_new_int64(procstats->event_handlers_enabled));
					json_object_object_add(programmstatus_object, "flap_detection_enabled",         json_object_new_int64(procstats->flap_detection_enabled));
					//Removed in nagios 4
					json_object_object_add(programmstatus_object, "failure_prediction_enabled",     json_object_new_int64(0));
					json_object_object_add(programmstatus_object, "process_performance_data",       json_object_new_int64(procstats->process_performance_data));
					json_object_object_add(programmstatus_object, "obsess_over_hosts",              json_object_new_int64(procstats->obsess_over_hosts));
					json_object_object_add(programmstatus_object, "obsess_over_services",           json_object_new_int64(procstats->obsess_over_services));
					json_object_object_add(programmstatus_object, "modified_host_attributes",       json_object_new_int64(procstats->modified_host_attributes));
					json_object_object_add(programmstatus_object, "modified_service_attributes",    json_object_new_int64(procstats->modified_service_attributes));

					json_object_object_add(my_object, "programmstatus", programmstatus_object);
					const char* json_string = json_object_to_json_string(my_object);
					ret= gearman_client_do_background(&client, "programmstatus", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
					if (ret != GEARMAN_SUCCESS)
						write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

					json_object_put(my_object);
				}
				break;

				case NEBCALLBACK_CONTACT_STATUS_DATA:
					if((contactstatus = (nebstruct_contact_status_data *)data)){
						if(contactstatus == NULL){
							return 0;
						}
						if((tmp_contact = (contact *)contactstatus->object_ptr) == NULL){
							return 0;
						}

						my_object = json_object_new_object();
						json_object_object_add(my_object, "type",      json_object_new_int(contactstatus->type));
						json_object_object_add(my_object, "flags",     json_object_new_int(contactstatus->flags));
						json_object_object_add(my_object, "attr",      json_object_new_int(contactstatus->attr));
						json_object_object_add(my_object, "timestamp", json_object_new_int(contactstatus->timestamp.tv_sec));

						json_object *contactstatus_object = json_object_new_object();
						json_object_object_add(contactstatus_object, "contact_name", (tmp_contact->name != NULL ? json_object_new_string(tmp_contact->name) : NULL));
						
						json_object_object_add(contactstatus_object, "host_notifications_enabled",    json_object_new_int64(tmp_contact->host_notifications_enabled));
						json_object_object_add(contactstatus_object, "service_notifications_enabled", json_object_new_int64(tmp_contact->service_notifications_enabled));
						json_object_object_add(contactstatus_object, "last_host_notification",        json_object_new_int64(tmp_contact->last_host_notification));
						json_object_object_add(contactstatus_object, "last_service_notification",     json_object_new_int64(tmp_contact->last_service_notification));
						json_object_object_add(contactstatus_object, "modified_attributes",           json_object_new_int64(tmp_contact->modified_attributes));
						json_object_object_add(contactstatus_object, "modified_host_attributes",      json_object_new_int64(tmp_contact->modified_host_attributes));
						json_object_object_add(contactstatus_object, "modified_service_attributes",   json_object_new_int64(tmp_contact->modified_service_attributes));

						json_object_object_add(my_object, "contactstatus", contactstatus_object);
						const char* json_string = json_object_to_json_string(my_object);
						ret= gearman_client_do_background(&client, "contactstatus", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
						if (ret != GEARMAN_SUCCESS)
							write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

						json_object_put(my_object);
					}
					break;
					
				case NEBCALLBACK_CONTACT_NOTIFICATION_DATA:
					if((cnd = (nebstruct_contact_notification_data *)data)){
						if(cnd == NULL){
							return 0;
						}
						my_object = json_object_new_object();
						json_object_object_add(my_object, "type",      json_object_new_int(cnd->type));
						json_object_object_add(my_object, "flags",     json_object_new_int(cnd->flags));
						json_object_object_add(my_object, "attr",      json_object_new_int(cnd->attr));
						json_object_object_add(my_object, "timestamp", json_object_new_int(cnd->timestamp.tv_sec));

						json_object *cnd_object = json_object_new_object();
						json_object_object_add(cnd_object, "host_name", (cnd->host_name != NULL ? json_object_new_string(cnd->host_name) : NULL));
						json_object_object_add(cnd_object, "service_description", (cnd->service_description != NULL ? json_object_new_string(cnd->service_description) : NULL));
						json_object_object_add(cnd_object, "output", (cnd->output != NULL ? json_object_new_string(cnd->output) : NULL));
						//May be this will exists one day?
						json_object_object_add(cnd_object, "long_output",  (cnd->output       != NULL ? json_object_new_string(cnd->output) : NULL));
						json_object_object_add(cnd_object, "ack_author",   (cnd->ack_author   != NULL ? json_object_new_string(cnd->ack_author) : NULL));
						json_object_object_add(cnd_object, "ack_data",     (cnd->ack_data     != NULL ? json_object_new_string(cnd->ack_data) : NULL));
						json_object_object_add(cnd_object, "contact_name", (cnd->contact_name != NULL ? json_object_new_string(cnd->contact_name) : NULL));

						json_object_object_add(cnd_object, "state",    json_object_new_int64(cnd->state));
						json_object_object_add(cnd_object, "reason_type",    json_object_new_int64(cnd->reason_type));
						json_object_object_add(cnd_object, "end_time",    json_object_new_int64(cnd->end_time.tv_sec));
						json_object_object_add(cnd_object, "start_time",    json_object_new_int64(cnd->start_time.tv_sec));
						json_object_object_add(cnd_object, "notification_type",    json_object_new_int64(cnd->notification_type));

						json_object_object_add(my_object, "contactnotificationdata", cnd_object);
						const char* json_string = json_object_to_json_string(my_object);
						ret= gearman_client_do_background(&client, "contactnotificationdata", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
						if (ret != GEARMAN_SUCCESS)
							write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

						json_object_put(my_object);
					}
					break;

				case NEBCALLBACK_CONTACT_NOTIFICATION_METHOD_DATA:
					if((cnm = (nebstruct_contact_notification_method_data *)data)){
						if(cnm == NULL){
							return 0;
						}
						my_object = json_object_new_object();
						json_object_object_add(my_object, "type",      json_object_new_int(cnm->type));
						json_object_object_add(my_object, "flags",     json_object_new_int(cnm->flags));
						json_object_object_add(my_object, "attr",      json_object_new_int(cnm->attr));
						json_object_object_add(my_object, "timestamp", json_object_new_int(cnm->timestamp.tv_sec));

						json_object *cnm_object = json_object_new_object();
						json_object_object_add(cnm_object, "host_name",           (cnm->host_name           != NULL ? json_object_new_string(cnm->host_name) : NULL));
						json_object_object_add(cnm_object, "service_description", (cnm->service_description != NULL ? json_object_new_string(cnm->service_description) : NULL));
						json_object_object_add(cnm_object, "output",              (cnm->output              != NULL ? json_object_new_string(cnm->output) : NULL));
						json_object_object_add(cnm_object, "ack_author",          (cnm->ack_author          != NULL ? json_object_new_string(cnm->ack_author) : NULL));
						json_object_object_add(cnm_object, "ack_data",            (cnm->ack_data            != NULL ? json_object_new_string(cnm->ack_data) : NULL));
						json_object_object_add(cnm_object, "contact_name",        (cnm->contact_name        != NULL ? json_object_new_string(cnm->contact_name) : NULL));
						json_object_object_add(cnm_object, "command_name",        (cnm->command_name        != NULL ? json_object_new_string(cnm->command_name) : NULL));
						json_object_object_add(cnm_object, "command_args",        (cnm->command_args        != NULL ? json_object_new_string(cnm->command_args) : NULL));

						json_object_object_add(cnm_object, "reason_type", json_object_new_int64(cnm->reason_type));
						json_object_object_add(cnm_object, "state",       json_object_new_int64(cnm->state));
						json_object_object_add(cnm_object, "start_time",  json_object_new_int64(cnm->start_time.tv_sec));
						json_object_object_add(cnm_object, "end_time",    json_object_new_int64(cnm->end_time.tv_sec));

						json_object_object_add(my_object, "contactnotificationmethod", cnm_object);
						const char* json_string = json_object_to_json_string(my_object);
						ret= gearman_client_do_background(&client, "contactnotificationmethod", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
						if (ret != GEARMAN_SUCCESS)
							write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

						json_object_put(my_object);
					}
					break;


		default:
			break;
		}

	return 0;
}

//Some precompiler tricks, to make the c&p easier ;-)
#define HOSTOBJECT_STRING(FIELD) \
	json_object_object_add(my_object, #FIELD, (temp_host->FIELD != NULL ? json_object_new_string(temp_host->FIELD) : NULL))

#define HOSTOBJECT_INT(FIELD) \
	json_object_object_add(my_object, #FIELD, json_object_new_int64(temp_host->FIELD))

#define SERVICEOBJECT_STRING(FIELD) \
	json_object_object_add(my_object, #FIELD, (temp_service->FIELD != NULL ? json_object_new_string(temp_service->FIELD) : NULL))

#define SERVICEOBJECT_INT(FIELD) \
	json_object_object_add(my_object, #FIELD, json_object_new_int64(temp_service->FIELD))


//Dump object data after programm start
void dump_object_data(){
	json_object *my_object;
	int x=0;

	//Nagios objects
	command *temp_command=NULL;
	timeperiod *temp_timeperiod=NULL;
	timerange *temp_timerange=NULL;
	contact *temp_contact=NULL;
	//commandsmember *temp_commandsmember=NULL;
	contactgroup *temp_contactgroup=NULL;
	host *temp_host=NULL;
	//?hostsmember *temp_hostsmember=NULL;
	//contactgroupsmember *temp_contactgroupsmember=NULL;
	hostgroup *temp_hostgroup=NULL;
	service *temp_service=NULL;
	servicegroup *temp_servicegroup=NULL;
	hostescalation *temp_hostescalation=NULL;
	serviceescalation *temp_serviceescalation=NULL;
	hostdependency *temp_hostdependency=NULL;
	servicedependency *temp_servicedependency=NULL;
	
	//contactsmember *temp_contactsmember=NULL;
	command *_command = NULL;
	commandsmember *contactcommand = NULL;
	//Fetch commands
	//Logging that we dump commands right now
	
	//Tell the woker, that were start object dumping
	my_object = json_object_new_object();
	json_object_object_add(my_object, "object_type",       json_object_new_int(100));
	const char* json_string_start = json_object_to_json_string(my_object);
	ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string_start, (size_t)strlen(json_string_start), NULL);
	if (ret != GEARMAN_SUCCESS)
		write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

	json_object_put(my_object);
	
	write_to_all_logs("[Statusengine] Dumping command configuration", NSLOG_INFO_MESSAGE);
	for(temp_command = command_list; temp_command != NULL; temp_command = temp_command->next){
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type", json_object_new_int(12));
		json_object_object_add(my_object, "command_name", json_object_new_string(temp_command->name));
		json_object_object_add(my_object, "command_line", json_object_new_string(temp_command->command_line));

		const char* json_string = json_object_to_json_string(my_object);

		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);

	}
	
	//Fetch timeperiods
	//Logging that we dump commands right now
	write_to_all_logs("[Statusengine] Dumping timeperiod configuration", NSLOG_INFO_MESSAGE);
	for(temp_timeperiod = timeperiod_list; temp_timeperiod != NULL; temp_timeperiod = temp_timeperiod->next){
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type", json_object_new_int(9));
		json_object_object_add(my_object, "name", json_object_new_string(temp_timeperiod->name));
		json_object_object_add(my_object, "alias", json_object_new_string(temp_timeperiod->alias));

		//Fetching timerange for current timeperiod
		json_object *timeranges = json_object_new_object();
		for(x=0;x<7;x++){
			char daystr[10];
			snprintf(daystr, 9, "%d", x);

			json_object *timerange_array = json_object_new_array();
			for(temp_timerange = temp_timeperiod->days[x]; temp_timerange != NULL; temp_timerange = temp_timerange->next){
				json_object *timerange_settings = json_object_new_object();
				json_object_object_add(timerange_settings, "start", json_object_new_int(temp_timerange->range_start));
				json_object_object_add(timerange_settings, "end",   json_object_new_int(temp_timerange->range_end));
				json_object_array_add(timerange_array, timerange_settings);
			}

			json_object_object_add(timeranges, daystr, timerange_array);
		}
		
		json_object_object_add(my_object, "timeranges", timeranges);
		const char* json_string = json_object_to_json_string(my_object);

		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);
		
	}
	
	//Fetch contact configuration
	write_to_all_logs("[Statusengine] Dumping contact configuration", NSLOG_INFO_MESSAGE);
	for(temp_contact = contact_list; temp_contact != NULL; temp_contact = temp_contact->next){
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type",                    json_object_new_int(10));
		json_object_object_add(my_object, "name",                           json_object_new_string(temp_contact->name));
		json_object_object_add(my_object, "alias",                          json_object_new_string(temp_contact->alias));
		json_object_object_add(my_object, "email",                          json_object_new_string(temp_contact->email));
		json_object_object_add(my_object, "pager",                          (temp_contact->pager != NULL ? json_object_new_string(temp_contact->pager) : NULL));
		json_object_object_add(my_object, "host_notification_period",       json_object_new_string(temp_contact->host_notification_period));
		json_object_object_add(my_object, "service_notification_period",    json_object_new_string(temp_contact->service_notification_period));
		json_object_object_add(my_object, "notify_on_service_downtime",     json_object_new_int(flag_isset(temp_contact->service_notification_options, OPT_DOWNTIME)));
		json_object_object_add(my_object, "notify_on_host_downtime",        json_object_new_int(flag_isset(temp_contact->host_notification_options,    OPT_DOWNTIME)));
		json_object_object_add(my_object, "host_notifications_enabled",     json_object_new_int(temp_contact->host_notifications_enabled));
		json_object_object_add(my_object, "service_notifications_enabled",  json_object_new_int(temp_contact->service_notifications_enabled));
		json_object_object_add(my_object, "can_submit_commands",            json_object_new_int(temp_contact->can_submit_commands));

		json_object_object_add(my_object, "notify_on_service_unknown",      json_object_new_int(flag_isset(temp_contact->service_notification_options, OPT_UNKNOWN)));
		json_object_object_add(my_object, "notify_on_service_warning",      json_object_new_int(flag_isset(temp_contact->service_notification_options, OPT_WARNING)));
		json_object_object_add(my_object, "notify_on_service_critical",     json_object_new_int(flag_isset(temp_contact->service_notification_options, OPT_CRITICAL)));
		json_object_object_add(my_object, "notify_on_service_recovery",     json_object_new_int(flag_isset(temp_contact->service_notification_options, OPT_RECOVERY)));
		json_object_object_add(my_object, "notify_on_service_flapping",     json_object_new_int(flag_isset(temp_contact->service_notification_options, OPT_FLAPPING)));
		json_object_object_add(my_object, "notify_on_host_unreachable",     json_object_new_int(flag_isset(temp_contact->host_notification_options,    OPT_UNREACHABLE)));
		json_object_object_add(my_object, "notify_on_host_down",            json_object_new_int(flag_isset(temp_contact->host_notification_options,    OPT_DOWN)));
		json_object_object_add(my_object, "notify_on_host_recovery",        json_object_new_int(flag_isset(temp_contact->host_notification_options,    OPT_RECOVERY)));
		json_object_object_add(my_object, "notify_on_host_flapping",        json_object_new_int(flag_isset(temp_contact->host_notification_options,    OPT_FLAPPING)));
		json_object_object_add(my_object, "minimum_value",                  json_object_new_int(temp_contact->minimum_value));

		//Fetch contact addresses
		json_object *address_array = json_object_new_array();
		for(x = 0; x < 6; x++){
			json_object_array_add(address_array, (temp_contact->address[x] != NULL ? json_object_new_string(temp_contact->address[x]) : NULL));
		}
		json_object_object_add(my_object, "address", address_array);

		//Fetch contact notification commands (host)
		json_object *hostcommands_array = json_object_new_array();
		
		//commandsmember *contactcommand = NULL;
		for(contactcommand = temp_contact->host_notification_commands; contactcommand != NULL; contactcommand = contactcommand->next){
			json_object *hostcommand_object = json_object_new_object();
			
			_command = contactcommand->command_ptr;
			if(_command != NULL){
				json_object_object_add(hostcommand_object, "command_name", json_object_new_string(_command->name));
				json_object_object_add(hostcommand_object, "command_line", json_object_new_string(_command->command_line));
				json_object_array_add(hostcommands_array, hostcommand_object);
			}
		}
		json_object_object_add(my_object, "host_commands", hostcommands_array);


		//Fetch contact notification commands (service)
		json_object *servicecommands_array = json_object_new_array();
		
		//commandsmember *contactcommand = NULL;
		for(contactcommand = temp_contact->service_notification_commands; contactcommand != NULL; contactcommand = contactcommand->next){
			json_object *servicecommand_object = json_object_new_object();
			
			_command = contactcommand->command_ptr;
			if(_command != NULL){
				json_object_object_add(servicecommand_object, "command_name", json_object_new_string(_command->name));
				json_object_object_add(servicecommand_object, "command_line", json_object_new_string(_command->command_line));
				json_object_array_add(servicecommands_array, servicecommand_object);
			}
		}
		
		json_object_object_add(my_object, "service_commands", servicecommands_array);


		const char* json_string = json_object_to_json_string(my_object);

		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);
	}

	//Fetch contact group configuration
	write_to_all_logs("[Statusengine] Dumping contact group configuration", NSLOG_INFO_MESSAGE);
	for(temp_contactgroup = contactgroup_list; temp_contactgroup != NULL; temp_contactgroup = temp_contactgroup->next){
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type",  json_object_new_int(11));
		json_object_object_add(my_object, "group_name",   json_object_new_string(temp_contactgroup->group_name));
		json_object_object_add(my_object, "alias",        json_object_new_string(temp_contactgroup->alias));

		contactsmember *temp_contactsmember = temp_contactgroup->members;

		json_object *contactgroup_contact_members_array = json_object_new_array();
		//Get the contacts of this contactgroup
		for(temp_contactsmember = temp_contactgroup->members; temp_contactsmember != NULL; temp_contactsmember = temp_contactsmember->next){
			json_object_array_add(contactgroup_contact_members_array, (temp_contactsmember->contact_name != NULL ? json_object_new_string(temp_contactsmember->contact_name) : NULL));
		}
		json_object_object_add(my_object, "contact_members", contactgroup_contact_members_array);

		const char* json_string = json_object_to_json_string(my_object);

		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);
	}
	
	//Fetch host configuration
	write_to_all_logs("[Statusengine] Dumping host configuration", NSLOG_INFO_MESSAGE);
	for(temp_host = host_list; temp_host != NULL; temp_host = temp_host->next){
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type",  json_object_new_int(1));
		HOSTOBJECT_STRING(name);
		HOSTOBJECT_STRING(alias);
		HOSTOBJECT_STRING(display_name);
		HOSTOBJECT_STRING(address);
		HOSTOBJECT_STRING(check_command);
		HOSTOBJECT_STRING(event_handler);
		HOSTOBJECT_STRING(notification_period);
		HOSTOBJECT_STRING(check_period);
		HOSTOBJECT_STRING(notes);
		HOSTOBJECT_STRING(notes_url);
		HOSTOBJECT_STRING(action_url);
		HOSTOBJECT_STRING(icon_image);
		HOSTOBJECT_STRING(icon_image_alt);
		HOSTOBJECT_STRING(vrml_image);
		HOSTOBJECT_STRING(statusmap_image);
		HOSTOBJECT_INT(have_3d_coords);
		HOSTOBJECT_INT(have_2d_coords);
		HOSTOBJECT_INT(x_2d);
		HOSTOBJECT_INT(y_2d);
		HOSTOBJECT_INT(x_3d);
		HOSTOBJECT_INT(y_3d);
		HOSTOBJECT_INT(z_3d);
		HOSTOBJECT_INT(first_notification_delay);
		HOSTOBJECT_INT(retry_interval);
		HOSTOBJECT_INT(notifications_enabled);
		HOSTOBJECT_INT(retain_nonstatus_information);
		HOSTOBJECT_INT(retain_status_information);
		HOSTOBJECT_INT(event_handler_enabled);
		HOSTOBJECT_INT(accept_passive_checks);
		HOSTOBJECT_INT(checks_enabled);
		HOSTOBJECT_INT(process_performance_data);
		HOSTOBJECT_INT(freshness_threshold);
		HOSTOBJECT_INT(check_freshness);
		HOSTOBJECT_INT(obsess);
		HOSTOBJECT_INT(hourly_value);
		HOSTOBJECT_INT(high_flap_threshold);
		HOSTOBJECT_INT(low_flap_threshold);
		HOSTOBJECT_INT(flap_detection_enabled);
		HOSTOBJECT_INT(notification_interval);
		HOSTOBJECT_INT(first_notification_delay);
		HOSTOBJECT_INT(max_attempts);
		HOSTOBJECT_INT(retry_interval);
		HOSTOBJECT_INT(check_interval);

		json_object_object_add(my_object, "flap_detection_on_up",          json_object_new_int(flag_isset(temp_host->flap_detection_options, OPT_UP)));
		json_object_object_add(my_object, "flap_detection_on_down",        json_object_new_int(flag_isset(temp_host->flap_detection_options, OPT_DOWN)));
		json_object_object_add(my_object, "flap_detection_on_unreachable", json_object_new_int(flag_isset(temp_host->flap_detection_options, OPT_UNREACHABLE)));

		json_object_object_add(my_object, "notify_on_down",                json_object_new_int(flag_isset(temp_host->notification_options,   OPT_DOWN)));
		json_object_object_add(my_object, "notify_on_unreachable",         json_object_new_int(flag_isset(temp_host->notification_options,   OPT_UNREACHABLE)));
		json_object_object_add(my_object, "notify_on_recovery",            json_object_new_int(flag_isset(temp_host->notification_options,   OPT_RECOVERY)));
		json_object_object_add(my_object, "notify_on_flapping",            json_object_new_int(flag_isset(temp_host->notification_options,   OPT_FLAPPING)));
		json_object_object_add(my_object, "notify_on_downtime",            json_object_new_int(flag_isset(temp_host->notification_options,   OPT_DOWNTIME)));

		json_object_object_add(my_object, "stalk_on_up",                   json_object_new_int(flag_isset(temp_host->stalking_options,       OPT_UP)));
		json_object_object_add(my_object, "stalk_on_down",                 json_object_new_int(flag_isset(temp_host->stalking_options,       OPT_DOWN)));
		json_object_object_add(my_object, "stalk_on_unreachable",          json_object_new_int(flag_isset(temp_host->stalking_options,       OPT_UNREACHABLE)));

		//Get parent hosts
		hostsmember *temp_hostsmember = temp_host->parent_hosts;
		json_object *parent_hosts_array = json_object_new_array();
		for(temp_hostsmember = temp_host->parent_hosts; temp_hostsmember != NULL; temp_hostsmember = temp_hostsmember->next){
			json_object_array_add(parent_hosts_array, (temp_hostsmember->host_name != NULL ? json_object_new_string(temp_hostsmember->host_name) : NULL));
		}
		json_object_object_add(my_object, "parent_hosts", parent_hosts_array);

		//Get contact groups
		contactgroupsmember *temp_contactgroupsmember = temp_host->contact_groups;
		json_object *contactgroups_array = json_object_new_array();
		for(temp_contactgroupsmember = temp_host->contact_groups; temp_contactgroupsmember != NULL; temp_contactgroupsmember = temp_contactgroupsmember->next){
			json_object_array_add(contactgroups_array, (temp_contactgroupsmember->group_name != NULL ? json_object_new_string(temp_contactgroupsmember->group_name) : NULL));
		}
		json_object_object_add(my_object, "contactgroups", contactgroups_array);
		
		//Get contacts
		contactsmember *temp_contacts = temp_host->contacts;
		json_object *contacts_array = json_object_new_array();
		for(temp_contacts = temp_host->contacts; temp_contacts != NULL; temp_contacts = temp_contacts->next){
			json_object_array_add(contacts_array, (temp_contacts->contact_name != NULL ? json_object_new_string(temp_contacts->contact_name) : NULL));
		}
		json_object_object_add(my_object, "contacts", contacts_array);

		//Get custom variables
		json_object *host_customvariables = json_object_new_object();
		customvariablesmember *temp_customvar = temp_host->custom_variables;
		for(temp_customvar = temp_host->custom_variables; temp_customvar != NULL; temp_customvar = temp_customvar->next){
			json_object_object_add(host_customvariables, temp_customvar->variable_name, json_object_new_string(temp_customvar->variable_value));
		}

		json_object_object_add(my_object, "custom_variables", host_customvariables);

		const char* json_string = json_object_to_json_string(my_object);

		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);
	}
	
	//Fetch hostgroup configuration
	write_to_all_logs("[Statusengine] Dumping host group configuration", NSLOG_INFO_MESSAGE);
	for(temp_hostgroup = hostgroup_list; temp_hostgroup != NULL; temp_hostgroup=temp_hostgroup->next){
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type",  json_object_new_int(3));
		json_object_object_add(my_object, "group_name",   (temp_hostgroup->group_name != NULL ? json_object_new_string(temp_hostgroup->group_name) : NULL));
		json_object_object_add(my_object, "alias",        (temp_hostgroup->alias      != NULL ? json_object_new_string(temp_hostgroup->alias) : NULL));

		//Get members
		//Get contact groups
		hostsmember *temp_hostsmember = temp_hostgroup->members;
		json_object *hostgroup_members_array = json_object_new_array();
		for(temp_hostsmember = temp_hostgroup->members; temp_hostsmember != NULL; temp_hostsmember = temp_hostsmember->next){
			json_object_array_add(hostgroup_members_array, (temp_hostsmember->host_name != NULL ? json_object_new_string(temp_hostsmember->host_name) : NULL));
		}
		json_object_object_add(my_object, "members", hostgroup_members_array);


		const char* json_string = json_object_to_json_string(my_object);

		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);
	}


	//Fetch service configuration
	write_to_all_logs("[Statusengine] Dumping service configuration", NSLOG_INFO_MESSAGE);
	for(temp_service = service_list; temp_service != NULL; temp_service = temp_service->next){
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type",  json_object_new_int(2));
		SERVICEOBJECT_STRING(host_name);
		SERVICEOBJECT_STRING(display_name);
		SERVICEOBJECT_STRING(description);
		SERVICEOBJECT_STRING(check_command);
		SERVICEOBJECT_STRING(event_handler);
		SERVICEOBJECT_STRING(notification_period);
		SERVICEOBJECT_STRING(check_period);
		SERVICEOBJECT_STRING(notes);
		SERVICEOBJECT_STRING(notes_url);
		SERVICEOBJECT_STRING(action_url);
		SERVICEOBJECT_STRING(icon_image);
		SERVICEOBJECT_STRING(icon_image_alt);
		SERVICEOBJECT_INT(first_notification_delay);
		SERVICEOBJECT_INT(check_interval);
		SERVICEOBJECT_INT(retry_interval);
		SERVICEOBJECT_INT(max_attempts);
		SERVICEOBJECT_INT(first_notification_delay);
		SERVICEOBJECT_INT(notification_interval);
		SERVICEOBJECT_INT(low_flap_threshold);
		SERVICEOBJECT_INT(process_performance_data);
		SERVICEOBJECT_INT(check_freshness);
		SERVICEOBJECT_INT(freshness_threshold);
		SERVICEOBJECT_INT(accept_passive_checks);
		SERVICEOBJECT_INT(event_handler_enabled);
		SERVICEOBJECT_INT(checks_enabled);
		SERVICEOBJECT_INT(retain_status_information);
		SERVICEOBJECT_INT(retain_nonstatus_information);
		SERVICEOBJECT_INT(notifications_enabled);
		SERVICEOBJECT_INT(obsess);
		SERVICEOBJECT_INT(hourly_value);
		SERVICEOBJECT_INT(high_flap_threshold);
		SERVICEOBJECT_INT(low_flap_threshold);
		SERVICEOBJECT_INT(flap_detection_enabled);

		json_object_object_add(my_object, "flap_detection_on_ok",          json_object_new_int(flag_isset(temp_service->flap_detection_options, OPT_OK)));
		json_object_object_add(my_object, "flap_detection_on_warning",     json_object_new_int(flag_isset(temp_service->flap_detection_options, OPT_WARNING)));
		json_object_object_add(my_object, "flap_detection_on_unknown",     json_object_new_int(flag_isset(temp_service->flap_detection_options, OPT_UNREACHABLE)));
		json_object_object_add(my_object, "flap_detection_on_critical",    json_object_new_int(flag_isset(temp_service->flap_detection_options, OPT_CRITICAL)));

		json_object_object_add(my_object, "notify_on_unknown",             json_object_new_int(flag_isset(temp_service->notification_options,   OPT_UNKNOWN)));
		json_object_object_add(my_object, "notify_on_warning",             json_object_new_int(flag_isset(temp_service->notification_options,   OPT_WARNING)));
		json_object_object_add(my_object, "notify_on_critical",            json_object_new_int(flag_isset(temp_service->notification_options,   OPT_CRITICAL)));
		json_object_object_add(my_object, "notify_on_recovery",            json_object_new_int(flag_isset(temp_service->notification_options,   OPT_RECOVERY)));
		json_object_object_add(my_object, "notify_on_flapping",            json_object_new_int(flag_isset(temp_service->notification_options,   OPT_FLAPPING)));
		json_object_object_add(my_object, "notify_on_downtime",            json_object_new_int(flag_isset(temp_service->notification_options,   OPT_DOWNTIME)));

		json_object_object_add(my_object, "stalk_on_ok",                   json_object_new_int(flag_isset(temp_service->stalking_options,       OPT_OK)));
		json_object_object_add(my_object, "stalk_on_warning",              json_object_new_int(flag_isset(temp_service->stalking_options,       OPT_WARNING)));
		json_object_object_add(my_object, "stalk_on_unknown",              json_object_new_int(flag_isset(temp_service->stalking_options,       OPT_UNKNOWN)));
		json_object_object_add(my_object, "stalk_on_critical",             json_object_new_int(flag_isset(temp_service->stalking_options,       OPT_CRITICAL)));

		//Get parent services
		servicesmember *temp_parent_services = temp_service->parents;
		json_object *parent_services_array = json_object_new_array();
		for(temp_parent_services = temp_service->parents; temp_parent_services != NULL; temp_parent_services = temp_parent_services->next){
			json_object *parent_service = json_object_new_object();
			json_object_object_add(parent_service, "host_name",           json_object_new_string(temp_parent_services->host_name));
			json_object_object_add(parent_service, "service_description", json_object_new_string(temp_parent_services->service_description));
			json_object_array_add(parent_services_array, parent_service);
		}
		json_object_object_add(my_object, "parent_services", parent_services_array);

		//Get contact groups
		contactgroupsmember *temp_contactgroupsmember = temp_service->contact_groups;
		json_object *contactgroups_array = json_object_new_array();
		for(temp_contactgroupsmember = temp_service->contact_groups; temp_contactgroupsmember != NULL; temp_contactgroupsmember = temp_contactgroupsmember->next){
			json_object_array_add(contactgroups_array, (temp_contactgroupsmember->group_name != NULL ? json_object_new_string(temp_contactgroupsmember->group_name) : NULL));
		}
		json_object_object_add(my_object, "contactgroups", contactgroups_array);

		//Get contacts
		contactsmember *temp_contacts = temp_service->contacts;
		json_object *contacts_array = json_object_new_array();
		for(temp_contacts = temp_service->contacts; temp_contacts != NULL; temp_contacts = temp_contacts->next){
			json_object_array_add(contacts_array, (temp_contacts->contact_name != NULL ? json_object_new_string(temp_contacts->contact_name) : NULL));
		}
		json_object_object_add(my_object, "contacts", contacts_array);

		//Get custom variables
		json_object *service_customvariables = json_object_new_object();
		customvariablesmember *temp_customvar = temp_service->custom_variables;
		for(temp_customvar = temp_service->custom_variables; temp_customvar != NULL; temp_customvar = temp_customvar->next){
			json_object_object_add(service_customvariables, temp_customvar->variable_name, json_object_new_string(temp_customvar->variable_value));
		}
		json_object_object_add(my_object, "custom_variables", service_customvariables);


		const char* json_string = json_object_to_json_string(my_object);

		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);
	}
	
	//Fetch service groups
	write_to_all_logs("[Statusengine] Dumping service group configuration", NSLOG_INFO_MESSAGE);
	for(temp_servicegroup = servicegroup_list; temp_servicegroup != NULL; temp_servicegroup = temp_servicegroup->next){
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type",  json_object_new_int(4));
		json_object_object_add(my_object, "group_name", (temp_servicegroup->group_name != NULL ? json_object_new_string(temp_servicegroup->group_name) : NULL));
		json_object_object_add(my_object, "alias",      (temp_servicegroup->alias != NULL ? json_object_new_string(temp_servicegroup->alias) : NULL));

		//Get service group members
		servicesmember *temp_servicegroupmember = temp_servicegroup->members;
		json_object *servicegroupmember_array = json_object_new_array();
		for(temp_servicegroupmember = temp_servicegroup->members; temp_servicegroupmember != NULL; temp_servicegroupmember = temp_servicegroupmember->next){
			json_object *member = json_object_new_object();
			json_object_object_add(member, "host_name",           json_object_new_string(temp_servicegroupmember->host_name));
			json_object_object_add(member, "service_description", json_object_new_string(temp_servicegroupmember->service_description));
			json_object_array_add(servicegroupmember_array, member);
		}
		json_object_object_add(my_object, "members", servicegroupmember_array);

		const char* json_string = json_object_to_json_string(my_object);

		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);
		
	}
	
	//Fetch host escalations
	write_to_all_logs("[Statusengine] Dumping host escalation configuration", NSLOG_INFO_MESSAGE);
	for(x = 0; x < num_objects.hostescalations; x++){
		temp_hostescalation = hostescalation_ary[x];
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type",       json_object_new_int(5));
		json_object_object_add(my_object, "host_name",         (temp_hostescalation->host_name         != NULL ? json_object_new_string(temp_hostescalation->host_name) : NULL));
		json_object_object_add(my_object, "escalation_period", (temp_hostescalation->escalation_period != NULL ? json_object_new_string(temp_hostescalation->escalation_period) : NULL));

		json_object_object_add(my_object, "last_notification",     json_object_new_int64(temp_hostescalation->last_notification));
		json_object_object_add(my_object, "first_notification",    json_object_new_int64(temp_hostescalation->first_notification));
		json_object_object_add(my_object, "notification_interval", json_object_new_int64(temp_hostescalation->notification_interval));

		json_object_object_add(my_object, "escalate_on_recovery",    json_object_new_int64(flag_isset(temp_hostescalation->escalation_options, OPT_RECOVERY)));
		json_object_object_add(my_object, "escalate_on_down",        json_object_new_int64(flag_isset(temp_hostescalation->escalation_options, OPT_DOWN)));
		json_object_object_add(my_object, "escalate_on_unreachable", json_object_new_int64(flag_isset(temp_hostescalation->escalation_options, OPT_UNREACHABLE)));

		//Get contact groups
		contactgroupsmember *temp_contactgroupsmember = temp_hostescalation->contact_groups;
		json_object *contactgroups_array = json_object_new_array();
		for(temp_contactgroupsmember = temp_hostescalation->contact_groups; temp_contactgroupsmember != NULL; temp_contactgroupsmember = temp_contactgroupsmember->next){
			json_object_array_add(contactgroups_array, (temp_contactgroupsmember->group_name != NULL ? json_object_new_string(temp_contactgroupsmember->group_name) : NULL));
		}
		json_object_object_add(my_object, "contactgroups", contactgroups_array);

		//Get contacts
		contactsmember *temp_contacts = temp_hostescalation->contacts;
		json_object *contacts_array = json_object_new_array();
		for(temp_contacts = temp_hostescalation->contacts; temp_contacts != NULL; temp_contacts = temp_contacts->next){
			json_object_array_add(contacts_array, (temp_contacts->contact_name != NULL ? json_object_new_string(temp_contacts->contact_name) : NULL));
		}
		json_object_object_add(my_object, "contacts", contacts_array);

		const char* json_string = json_object_to_json_string(my_object);
	
		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);
	}
	
	//Fetch service escalations
	write_to_all_logs("[Statusengine] Dumping servcie escalation configuration", NSLOG_INFO_MESSAGE);
	for(x = 0; x < num_objects.serviceescalations; x++) {
		temp_serviceescalation = serviceescalation_ary[x];
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type",       json_object_new_int(6));
		json_object_object_add(my_object, "host_name",         (temp_serviceescalation->host_name         != NULL ? json_object_new_string(temp_serviceescalation->host_name) : NULL));
		json_object_object_add(my_object, "description",       (temp_serviceescalation->description         != NULL ? json_object_new_string(temp_serviceescalation->description) : NULL));
		json_object_object_add(my_object, "escalation_period", (temp_serviceescalation->escalation_period         != NULL ? json_object_new_string(temp_serviceescalation->escalation_period) : NULL));
		
		json_object_object_add(my_object, "first_notification",    json_object_new_int64(temp_serviceescalation->first_notification));
		json_object_object_add(my_object, "last_notification",     json_object_new_int64(temp_serviceescalation->last_notification));
		json_object_object_add(my_object, "notification_interval", json_object_new_int64(temp_serviceescalation->notification_interval));
		
		json_object_object_add(my_object, "escalate_on_recovery", json_object_new_int64(flag_isset(temp_serviceescalation->escalation_options, OPT_RECOVERY)));
		json_object_object_add(my_object, "escalate_on_warning",  json_object_new_int64(flag_isset(temp_serviceescalation->escalation_options, OPT_WARNING)));
		json_object_object_add(my_object, "escalate_on_unknown",  json_object_new_int64(flag_isset(temp_serviceescalation->escalation_options, OPT_UNKNOWN)));
		json_object_object_add(my_object, "escalate_on_critical", json_object_new_int64(flag_isset(temp_serviceescalation->escalation_options, OPT_CRITICAL)));
		
		//Get contact groups
		contactgroupsmember *temp_contactgroupsmember = temp_serviceescalation->contact_groups;
		json_object *contactgroups_array = json_object_new_array();
		for(temp_contactgroupsmember = temp_serviceescalation->contact_groups; temp_contactgroupsmember != NULL; temp_contactgroupsmember = temp_contactgroupsmember->next){
			json_object_array_add(contactgroups_array, (temp_contactgroupsmember->group_name != NULL ? json_object_new_string(temp_contactgroupsmember->group_name) : NULL));
		}
		json_object_object_add(my_object, "contactgroups", contactgroups_array);

		//Get contacts
		contactsmember *temp_contacts = temp_serviceescalation->contacts;
		json_object *contacts_array = json_object_new_array();
		for(temp_contacts = temp_serviceescalation->contacts; temp_contacts != NULL; temp_contacts = temp_contacts->next){
			json_object_array_add(contacts_array, (temp_contacts->contact_name != NULL ? json_object_new_string(temp_contacts->contact_name) : NULL));
		}
		json_object_object_add(my_object, "contacts", contacts_array);

		const char* json_string = json_object_to_json_string(my_object);
		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);
	}
	
	write_to_all_logs("[Statusengine] Dumping host dependency configuration", NSLOG_INFO_MESSAGE);
	for(x = 0; x < num_objects.hostdependencies; x++){
		temp_hostdependency = hostdependency_ary[x];
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type",         json_object_new_int(7));
		json_object_object_add(my_object, "host_name",           (temp_hostdependency->host_name           != NULL ? json_object_new_string(temp_hostdependency->host_name) : NULL));
		json_object_object_add(my_object, "dependent_host_name", (temp_hostdependency->dependent_host_name != NULL ? json_object_new_string(temp_hostdependency->dependent_host_name) : NULL));
		json_object_object_add(my_object, "dependency_period",   (temp_hostdependency->dependency_period   != NULL ? json_object_new_string(temp_hostdependency->dependency_period) : NULL));
		
		json_object_object_add(my_object, "dependency_type", json_object_new_int64(temp_hostdependency->dependency_type));
		json_object_object_add(my_object, "inherits_parent", json_object_new_int64(temp_hostdependency->inherits_parent));
		
		json_object_object_add(my_object, "fail_on_up",          json_object_new_int64(flag_isset(temp_hostdependency->failure_options, OPT_UP)));
		json_object_object_add(my_object, "fail_on_down",        json_object_new_int64(flag_isset(temp_hostdependency->failure_options, OPT_DOWN)));
		json_object_object_add(my_object, "fail_on_unreachable", json_object_new_int64(flag_isset(temp_hostdependency->failure_options, OPT_UNREACHABLE)));
		
		const char* json_string = json_object_to_json_string(my_object);
		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);
	}

	write_to_all_logs("[Statusengine] Dumping service dependency configuration", NSLOG_INFO_MESSAGE);
	for(x = 0; x < num_objects.servicedependencies; x++){
		temp_servicedependency = servicedependency_ary[x];
		my_object = json_object_new_object();
		json_object_object_add(my_object, "object_type",                   json_object_new_int(8));
		json_object_object_add(my_object, "host_name",                     (temp_servicedependency->host_name                     != NULL ? json_object_new_string(temp_servicedependency->host_name) : NULL));
		json_object_object_add(my_object, "service_description",           (temp_servicedependency->service_description           != NULL ? json_object_new_string(temp_servicedependency->service_description) : NULL));
		json_object_object_add(my_object, "dependent_host_name",           (temp_servicedependency->dependent_host_name           != NULL ? json_object_new_string(temp_servicedependency->dependent_host_name) : NULL));
		json_object_object_add(my_object, "dependent_service_description", (temp_servicedependency->dependent_service_description != NULL ? json_object_new_string(temp_servicedependency->dependent_service_description) : NULL));

		json_object_object_add(my_object, "dependent_host_name", (temp_servicedependency->dependent_host_name != NULL ? json_object_new_string(temp_servicedependency->dependent_host_name) : NULL));
		json_object_object_add(my_object, "dependency_period",   (temp_servicedependency->dependency_period   != NULL ? json_object_new_string(temp_servicedependency->dependency_period) : NULL));

		json_object_object_add(my_object, "dependency_type", json_object_new_int64(temp_servicedependency->dependency_type));
		json_object_object_add(my_object, "inherits_parent", json_object_new_int64(temp_servicedependency->inherits_parent));
		
		json_object_object_add(my_object, "fail_on_ok",       json_object_new_int64(flag_isset(temp_servicedependency->failure_options, OPT_OK)));
		json_object_object_add(my_object, "fail_on_warning",  json_object_new_int64(flag_isset(temp_servicedependency->failure_options, OPT_WARNING)));
		json_object_object_add(my_object, "fail_on_unknown",  json_object_new_int64(flag_isset(temp_servicedependency->failure_options, OPT_UNKNOWN)));
		json_object_object_add(my_object, "fail_on_critical", json_object_new_int64(flag_isset(temp_servicedependency->failure_options, OPT_CRITICAL)));

		const char* json_string = json_object_to_json_string(my_object);
		ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
		if (ret != GEARMAN_SUCCESS)
			write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);

		json_object_put(my_object);
	}
	
	
	//Tell the woker, that were done with object dumping
	my_object = json_object_new_object();
	json_object_object_add(my_object, "object_type",       json_object_new_int(101));
	const char* json_string = json_object_to_json_string(my_object);
	ret= gearman_client_do_background(&client, "objects", NULL, (void *)json_string, (size_t)strlen(json_string), NULL);
	if (ret != GEARMAN_SUCCESS)
		write_to_all_logs((char *)gearman_client_error(&client), NSLOG_INFO_MESSAGE);
	json_object_put(my_object);
	
}
