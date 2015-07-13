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
*                             Database clenup cronjob
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
* This little extension allows you, to store performance data out of Mod_Gearmans perfdata Q
* I know process_perfdata.pl can do this job as well, but on my test systems with 50k and 220k
* service checks i received millions of GEARMAN_UNEXPECTED_PACKET errors and my Gearman Job Server
* crashed.
*
* This is why i started coding this php script, to get the job done :)
*
* --------------------------------------------------------------------------------
*
* --------------------------------------------------------------------------------
* HOW TO START:
* sudo -u www-data /opt/statusengine/cakephp/app/Console/cake cronjob
*
**********************************************************************************/

class CronjobShell extends AppShell{

	public $uses = [
		'Legacy.Hostcheck',
		'Legacy.Servicecheck',
		'Legacy.Statehistory',
		'Legacy.Acknowledgement',
		'Legacy.Logentry',
		'Legacy.Downtimehistory',
		'Legacy.Contactnotificationmethod',
		'Legacy.Contactnotification',
		'Legacy.Notification',
	];

	public function main(){
		Configure::load('Cronjob');
		$this->cleanupHostchecks();
		$this->cleanupServicechecks();
		$this->cleanupStatehistory();
		$this->cleanupAcknowledgements();
		$this->cleanupLogentries();
		$this->cleanupDowntimes();
		$this->cleanupNotifications();
	}

	public function cleanupHostchecks(){
		$this->Hostcheck->deleteAll([
			'Hostcheck.start_time_usec < ' => (time() - (int)Configure::read('Cleanup.hostchecks'))
		]);
	}

	public function cleanupServicechecks(){
		$this->Servicecheck->deleteAll([
			'Servicecheck.start_time_usec < ' => (time() - (int)Configure::read('Cleanup.servicechecks'))
		]);
	}

	public function cleanupStatehistory(){
		$this->Statehistory->deleteAll([
			'Statehistory.state_time_usec < ' => (time() - (int)Configure::read('Cleanup.statehistory'))
		]);
	}

	public function cleanupAcknowledgements(){
		$this->Acknowledgement->deleteAll([
			'Acknowledgement.entry_time_usec < ' => (time() - (int)Configure::read('Cleanup.acknowledgements'))
		]);
	}

	public function cleanupLogentries(){
		$this->Logentry->deleteAll([
			'Logentry.entry_time_usec < ' => (time() - (int)Configure::read('Cleanup.logentries'))
		]);
	}

	public function cleanupDowntimes(){
		$this->Downtimehistory->deleteAll([
			'Downtimehistory.scheduled_end_time < ' => date('Y-m-d H:i:s', time() - (int)Configure::read('Cleanup.downtimes'))
		]);
	}

	public function cleanupNotifications(){
		$time = (time() - (int)Configure::read('Cleanup.notifications'));
		$this->Contactnotificationmethod->deleteAll([
			'Contactnotificationmethod.start_time_usec < ' => $time
		]);
		$this->Contactnotification->deleteAll([
			'Contactnotification.start_time_usec < ' => $time
		]);
		$this->Notification->deleteAll([
			'Notification.start_time_usec < ' => $time
		]);
	}
}
