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
*            Perfdata Extension for Mod_Gearaman (http://mod-gearman.org)
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
* REQUIREMENTS:
*
* apt-get install php5-mcrypt php5-rrd
* php5enmod mcrypt
*
* --------------------------------------------------------------------------------
* HOW TO START:
* /opt/statusengine/cakephp/app/Console/cake mod_perfdata
*
**********************************************************************************/

class ModPerfdataShell extends AppShell{

	public $tasks = [
		'Perfdata',
		'RrdtoolBackend'
	];

	//Some class variables
	protected $worker = null;
	private $maxJobIdleCounter = 250;
	private $childPids = [];

	public $Config = [];

	/**
	 * Gets called if a user run the shell over Console/cake mod_perfdata
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function main(){
		Configure::load('Perfdata');
		$this->Config = Configure::read('perfdata');

		//Load CakePHP's File class
		App::uses('File', 'Utility');

		$this->out('Starting Statusengine ModPerfdata extension -  version: '.PERFDATA_VERSION.'...');
		if($this->Config['MOD_GEARMAN']['encryption'] === true){
			//Mod_Gearman use a 32bit key to encrypt data, if encryption is enabled
			//Mod_Gearman data is crypted with AES 128 bit
			$this->fillKeyWithZero();
		}

		//Some testing perfdata
		//debug($this->Perfdata->parsePerfdataString('rta=0.069000ms;100.000000;500.000000;0.000000 pl=0%;20;60;0'));die();
		//debug($this->Perfdata->parsePerfdataString('active=650;jobs=650;worker=3436;queues=29'));die();
		//debug($this->Perfdata->parseCheckCommand('84084403-5c21-4273-835b-d8ac770b4a9f!7.0,6.0,5.0!10.0,7.0,6.0'));die();

		$this->RrdtoolBackend->init($this->Config);

		$this->forkWorkers();

		$this->createWorker();
		$this->work();
	}

	public function forkWorkers(){
		declare(ticks = 1);
		if($this->Config['worker'] > 1){
			for($i = 1; $i < $this->Config['worker']; $i++){
				CakeLog::info('Forking a new worker child');
				$pid = pcntl_fork();
				if(!$pid){
					CakeLog::info('Hey, I\'m a new child');
					pcntl_signal(SIGTERM, [$this, 'childSignalHandler']);
					//Run while(true) to prevent a forkcalypse
					$this->createWorker();
					$this->work();
				}else{
					//we are the parrent
					$this->childPids[] = $pid;
				}
			}
		}

		pcntl_signal(SIGTERM, [$this, 'signalHandler']);
		pcntl_signal(SIGINT,  [$this, 'signalHandler']);
	}

	/**
	 * This is the parent signal handel, so that we can catch SIGTERM and SIGINT
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function signalHandler($signo){
		switch($signo){
			case SIGINT:
			case SIGTERM:
				CakeLog::info('Will kill my childs :-(');
				$this->sendSignal(SIGTERM);
				CakeLog::info('Bye');
				exit(0);
				break;
		}
	}

	/**
	 * This function sends a singal to every child process
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function sendSignal($signal){
		if($signal !== SIGTERM){
			foreach($this->childPids as $cpid){
				CakeLog::info('Send signal to child pid: '.$cpid);
				posix_kill($cpid, $signal);
			}
		}

		if($signal == SIGTERM){
			foreach($this->childPids as $cpid){
				CakeLog::info('Will kill pid: '.$cpid);
				posix_kill($cpid, SIGTERM);
			}
			foreach($this->childPids as $cpid){
				pcntl_waitpid($cpid, $status);
				CakeLog::info('Child ['.$cpid.'] killed successfully');
			}
		}
	}

	public function childSignalHandler($signo){
		CakeLog::info('Recived signal: '.$signo);
		switch($signo){
			case SIGTERM:
				CakeLog::info('Will kill myself :-(');
				CakeLog::info('Unregister all my queues');
				exit(0);
				break;
		}
	}

	/**
	 * Create the GearmanWorker that is responsible for the perfdata Q
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function createWorker(){
		$this->worker = new GearmanWorker();
		$this->worker->addOptions(GEARMAN_WORKER_NON_BLOCKING);
		$this->worker->addServer($this->Config['GEARMAN']['server'], $this->Config['GEARMAN']['port']);
		$this->worker->addFunction('perfdata', [$this, 'processPerfdata']);
	}

	/**
	 * Will fill up a key < 32 bit with zero, that we are able to uncrypt the
	 * data stored in gearman job server (provided by mod_gearman)
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function fillKeyWithZero(){
		$key = $this->Config['MOD_GEARMAN']['key'];
		while(strlen($key) < 32){
			$key .= chr(0);
		}
		$this->Config['MOD_GEARMAN']['key'] = $key;
	}

	/**
	 * Uncrypt the data recived form gearman
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return string
	 */
	public function decrypt($stringFormModGearman){
		return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->Config['MOD_GEARMAN']['key'], base64_decode($stringFormModGearman), MCRYPT_MODE_ECB);
	}

	/**
	 * Run GearmanWoker::work()
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function work(){
		$jobIdleCounter = 0;
		while(true){
			pcntl_signal_dispatch();
			$this->worker->work();
			if($this->worker->returnCode() == GEARMAN_NO_JOBS || $this->worker->returnCode() == GEARMAN_IO_WAIT){
				if($jobIdleCounter < $this->maxJobIdleCounter){
					$jobIdleCounter++;
				}
			}else{
				$jobIdleCounter = 0;
			}

			if($jobIdleCounter === $this->maxJobIdleCounter){
				//Save some CPU time
				sleep(1);
			}
		}
	}

	/**
	 * Is the callback function, called by GearmanWorker::work()
	 * Will parse and process performance data
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function processPerfdata($job){
		$stringFormModGearman = $job->workload();

		if($this->Config['MOD_GEARMAN']['encryption'] === true){
			$stringFormModGearman = $this->decrypt($stringFormModGearman);
		}else{
			$stringFormModGearman = base64_decode($stringFormModGearman);
		}

		$parsedPerfdataString = $this->parsePerfdataFileTemplate($stringFormModGearman);
		if(isset($parsedPerfdataString['SERVICEPERFDATA'])){
			$parsedPerfdata = $this->Perfdata->parsePerfdataString($parsedPerfdataString['SERVICEPERFDATA']);
			$rrdReturn = $this->RrdtoolBackend->writeToRrd($parsedPerfdataString, $parsedPerfdata);
			if($this->Config['XML']['write_xml_files'] === true){
				$this->RrdtoolBackend->updateXml($parsedPerfdataString, $parsedPerfdata, $rrdReturn);
			}
		}
	}


	/**
	 * Parse perfdata out of service_perfdata_file_template defined in naemon.cfg to an array
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function parsePerfdataFileTemplate($perfdataFileTemplateString){
		/* $perfdataFileTemplateString should looks like this
		 * DATATYPE::SERVICEPERFDATA	TIMET::1431363160	HOSTNAME::localhost	SERVICEDESC::ping	SERVICEPERFDATA::rta=0.069000ms;100.000000;500.000000;0.000000 pl=0%;20;60;0	SERVICECHECKCOMMAND::check_ping!100.0,20%!500.0,60%	SERVICESTATE::0	SERVICESTATETYPE::1
		*/

		$keyValuePairs = explode("\t", trim($perfdataFileTemplateString));
		$parsedData = [];
		foreach($keyValuePairs as $keyValuePair){
			$result = explode('::', $keyValuePair);
			$parsedData[$result[0]] = $result[1];
		}
		return $parsedData;
	}

}
