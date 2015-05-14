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
	
	public $tasks = ['Logfile'];
	
	//Some class variables
	protected $worker = null;
	private $maxJobIdleCounter = 250;
	private $childPids = [];
	
	public $Config = [];
	
	public $servicestate = [
		0 => 'OK',
		1 => 'WARNING', 
		2 => 'CRITICAL',
		3 => 'UNKNOWN'
	];
	
	
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
		
		$this->Logfile->init($this->Config['logfile']);
		$this->Logfile->welcome();
		
		//Load CakePHP's File class
		App::uses('File', 'Utility');
		
		$this->out('Starting Statusengine ModPerfdata extension -  version: '.$this->Config['version'].'...');
		if($this->Config['MOD_GEARMAN']['encryption'] === true){
			//Mod_Gearman use a 32bit key to encrypt data, if encryption is enabled
			//Mod_Gearman data is crypted with AES 128 bit
			$this->fillKeyWithZero();
		}
		
		//Some testing perfdata
		//debug($this->parsePerfdataString('rta=0.069000ms;100.000000;500.000000;0.000000 pl=0%;20;60;0'));die();
		//debug($this->parsePerfdataString('active=650;jobs=650;worker=3436;queues=29'));die();
		//debug($this->parseCheckCommand('84084403-5c21-4273-835b-d8ac770b4a9f!7.0,6.0,5.0!10.0,7.0,6.0'));die();
		
		$this->forkWorkers();
		
		$this->createWorker();
		$this->work();
	}
	
	public function forkWorkers(){
		declare(ticks = 1);
		if($this->Config['worker'] > 1){
			for($i = 1; $i < $this->Config['worker']; $i++){
				$this->Logfile->stlog('Forking a new worker child');
				$pid = pcntl_fork();
				if(!$pid){
					$this->Logfile->clog('Hey, I\'m a new child');
					pcntl_signal(SIGTERM, [$this, 'childSignalHandler']);
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
				$this->Logfile->stlog('Will kill my childs :-(');
				$this->sendSignal(SIGTERM);
				$this->Logfile->stlog('Bye');
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
				$this->Logfile->stlog('Send signal to child pid: '.$cpid);
				posix_kill($cpid, $signal);
			}
		}

		if($signal == SIGTERM){
			foreach($this->childPids as $cpid){
				$this->Logfile->stlog('Will kill pid: '.$cpid);
				posix_kill($cpid, SIGTERM);
			}
			foreach($this->childPids as $cpid){
				pcntl_waitpid($cpid, $status);
				$this->Logfile->stlog('Child ['.$cpid.'] killed successfully');
			}
		}
	}
	
	public function childSignalHandler($signo){
		$this->Logfile->clog('Recived signal: '.$signo);
		switch($signo){
			case SIGTERM:
				$this->Logfile->clog('Will kill myself :-(');
				$this->Logfile->clog('Unregister all my queues');
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
			$parsedPerfdata = $this->parsePerfdataString($parsedPerfdataString['SERVICEPERFDATA']);
			$rrdReturn = $this->writeToRrd($parsedPerfdataString, $parsedPerfdata);
			if($this->Config['XML']['write_xml_files'] === true){
				$this->updateXml($parsedPerfdataString, $parsedPerfdata, $rrdReturn);
			}
		}
	}
	
	public function writeToRrd($parsedPerfdataString, $parsedPerfdata){
		if(!is_dir($this->Config['PERFDATA']['dir'].$parsedPerfdataString['HOSTNAME'])){
			mkdir($this->Config['PERFDATA']['dir'].$parsedPerfdataString['HOSTNAME']);
		}
		
		$perfdataFile = $this->Config['PERFDATA']['dir'].$parsedPerfdataString['HOSTNAME'].'/'.$parsedPerfdataString['SERVICEDESC'].'.rrd';
		$error = '';
		$return = true;
		
		if(file_exists($perfdataFile)){
			$options = [];
			$options[] = $parsedPerfdataString['TIMET'];
			
			foreach($parsedPerfdata as $ds => $data){
				$options[] = $data['current'];
			}

			if(!rrd_update($perfdataFile, [implode(':', $options)])){
				$this->out('Error on updating RRD');
				$return = false;
				$error = rrd_error();
				$this->Logfile->stlog($error);
				//debug($error);
			}
		}else{
			//RRA:AVERAGE:0.5:1:576000 RRA:MAX:0.5:1:576000 RRA:MIN:0.5:1:576000 DS:1:GAUGE:8460:U:U --start=1431375240 --step=60
			//RRA:AVERAGE:0.5:1:576000 RRA:MAX:0.5:1:576000 RRA:MIN:0.5:1:576000 DS:1:GAUGE:8460:U:U DS:2:GAUGE:8460:U:U --start=1431375345 --step=60
			$options = [];
			$options[] = 'RRA:AVERAGE:'.$this->Config['RRA']['average'];
			$options[] = 'RRA:MAX:'.$this->Config['RRA']['max'];
			$options[] = 'RRA:MIN:'.$this->Config['RRA']['min'];
			
			$dataSourceCount = 1;
			foreach($parsedPerfdata as $ds => $data){
				if(isset($this->Config['RRD']['DATATYPE'][$data['unit']])){
					$options[] = 'DS:'.$dataSourceCount.':'.$this->Config['RRD']['DATATYPE'][$data['unit']].':8460:U:U';
				}else{
					$options[] = 'DS:'.$dataSourceCount.':'.$this->Config['RRD']['DATATYPE']['default'].':8460:U:U';
				}
				$dataSourceCount++;
			}
			
			$options[] = '--start='.$parsedPerfdataString['TIMET'];
			$options[] = '--step='.$this->Config['RRA']['step'];
			
			if(!rrd_create($perfdataFile, $options)){
				$this->out('Error on createing RRD');
				$return = false;
				$error = rrd_error();
				$this->Logfile->stlog($error);
				//debug($error);
			}
		}
		
		return [
			'return' => $return,
			'error' => $error
		];
		
	}
	
	public function updateXML($parsedPerfdataString, $parsedPerfdata, $rrdReturn){
		$xmlFile = new File($this->Config['PERFDATA']['dir'].$parsedPerfdataString['HOSTNAME'].'/'.$parsedPerfdataString['SERVICEDESC'].'.xml');
		if(!$xmlFile->exists()){
			$xmlFile->create();
		}
		
		if($this->Config['XML']['delay'] > 0){
			if((time() - $xmlFile->lastChange()) < $this->Config['XML']['delay']){
				return false;
			}
		}
		
		$xml = "";

$xml .= "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
<NAGIOS>";
$dataSourceCounter = 1;
$template = $this->parseCheckCommand($parsedPerfdataString['SERVICECHECKCOMMAND'])[0];
foreach($parsedPerfdata as $ds => $data){
$xml.="  <DATASOURCE>
    <TEMPLATE>".$template."</TEMPLATE>
    <RRDFILE>".$this->Config['PERFDATA']['dir'].$parsedPerfdataString['HOSTNAME'].'/'.$parsedPerfdataString['SERVICEDESC'].".rrd</RRDFILE>
    <RRD_STORAGE_TYPE>SINGLE</RRD_STORAGE_TYPE>
    <RRD_HEARTBEAT>".$this->Config['RRD']['heartbeat']."</RRD_HEARTBEAT>
    <IS_MULTI>0</IS_MULTI>
    <DS>".$dataSourceCounter."</DS>
    <NAME>".$ds."</NAME>
    <LABEL>".$ds."</LABEL>
    <UNIT>".$data['unit']."</UNIT>
    <ACT>".$data['current']."</ACT>
    <WARN>".$data['warning']."</WARN>
    <WARN_MIN></WARN_MIN>
    <WARN_MAX></WARN_MAX>
    <WARN_RANGE_TYPE></WARN_RANGE_TYPE>
    <CRIT>".$data['critical']."</CRIT>
    <CRIT_MIN></CRIT_MIN>
    <CRIT_MAX></CRIT_MAX>
    <CRIT_RANGE_TYPE></CRIT_RANGE_TYPE>
    <MIN>".$data['min']."</MIN>
    <MAX>".$data['max']."</MAX>
  </DATASOURCE>";
  $dataSourceCounter++;
}

$xml.="  <RRD>
    <RC>".(int)$rrdReturn['return']."</RC>
    <TXT>".($rrdReturn['return']?'successful updated':$rrdReturn['error'])."</TXT>
  </RRD>
  <NAGIOS_AUTH_HOSTNAME></NAGIOS_AUTH_HOSTNAME>
  <NAGIOS_AUTH_SERVICEDESC></NAGIOS_AUTH_SERVICEDESC>
  <NAGIOS_CHECK_COMMAND>".$parsedPerfdataString['SERVICECHECKCOMMAND']."</NAGIOS_CHECK_COMMAND>
  <NAGIOS_DATATYPE>".$parsedPerfdataString['DATATYPE']."</NAGIOS_DATATYPE>
  <NAGIOS_DISP_HOSTNAME>".$parsedPerfdataString['HOSTNAME']."</NAGIOS_DISP_HOSTNAME>
  <NAGIOS_DISP_SERVICEDESC>".$parsedPerfdataString['SERVICEDESC']."</NAGIOS_DISP_SERVICEDESC>
  <NAGIOS_HOSTNAME>".$parsedPerfdataString['HOSTNAME']."</NAGIOS_HOSTNAME>
  <NAGIOS_HOSTSTATE></NAGIOS_HOSTSTATE>
  <NAGIOS_HOSTSTATETYPE></NAGIOS_HOSTSTATETYPE>
  <NAGIOS_MULTI_PARENT></NAGIOS_MULTI_PARENT>
  <NAGIOS_PERFDATA>".$parsedPerfdataString['SERVICEPERFDATA']."</NAGIOS_PERFDATA>
  <NAGIOS_RRDFILE>".$this->Config['PERFDATA']['dir'].$parsedPerfdataString['HOSTNAME'].'/'.$parsedPerfdataString['SERVICEDESC'].".rrd</NAGIOS_RRDFILE>
  <NAGIOS_SERVICECHECKCOMMAND>".$parsedPerfdataString['SERVICECHECKCOMMAND']."</NAGIOS_SERVICECHECKCOMMAND>
  <NAGIOS_SERVICEDESC>".$parsedPerfdataString['SERVICEDESC']."</NAGIOS_SERVICEDESC>
  <NAGIOS_SERVICEPERFDATA>".$parsedPerfdataString['SERVICEPERFDATA']."</NAGIOS_SERVICEPERFDATA>
  <NAGIOS_SERVICESTATE>OK</NAGIOS_SERVICESTATE>
  <NAGIOS_SERVICESTATETYPE>".($parsedPerfdataString['SERVICESTATETYPE'] == 1 ? 'HARD' : 'SOFT')."</NAGIOS_SERVICESTATETYPE>
  <NAGIOS_TIMET>".$parsedPerfdataString['TIMET']."</NAGIOS_TIMET>
  <NAGIOS_XMLFILE>".$this->Config['PERFDATA']['dir'].$parsedPerfdataString['HOSTNAME'].'/'.$parsedPerfdataString['SERVICEDESC'].".xml</NAGIOS_XMLFILE>
  <XML>
   <VERSION>4</VERSION>
  </XML>
</NAGIOS>";

	$xmlFile->write($xml);
	$xmlFile->close();

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
	
	/**
	 * Parse perfdata of the naemon plugin output to an array
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	function parsePerfdataString($perfdataString){
		/* $perfdataString should looks like this
		rta=0.069000ms;100.000000;500.000000;0.000000 pl=0%;20;60;0
		 */
		$perfdata = [];
		$arrayKeys = [
			'current', 'unit', 'warning', 'critical', 'min', 'max'
		];
		
		$defaultValues = [
			'current' => null,
			'unit' => null,
			'warning' => null,
			'critical' => null,
			'min' => null,
			'max' => null
		];
		
		foreach(explode(" ", $perfdataString) as $dataSource){
			$i = 2;
			foreach(explode(';', $dataSource) as $value){
				if(preg_match('/=/', $value)){
					$s = preg_split('/=/', $value);
					//Fetch unit
					$current = '';
					$unit = '';
					foreach(str_split($s[1]) as $char ){
						if( $char == '.' || $char == ',' || ($char >= '0' && $char <= '9') ){
							$current .= $char;
						}else{
							$unit .= $char;
						}
					}
					
					if($unit == '%'){
						$unit = '%%';
					}
					
					$perfdata[$s[0]][$arrayKeys[0]] = str_replace(',', '.', $current);
					$perfdata[$s[0]][$arrayKeys[1]] = $unit;
					continue;
				}
				
				$perfdata[$s[0]][$arrayKeys[$i]] = $value;
				$i++;
			}
			unset($s);
		}
		
		//Fil up missing fields in array
		foreach($perfdata as $dataSource => $values){
			$perfdata[$dataSource] = array_merge($defaultValues, $perfdata[$dataSource]);
		}

		return $perfdata;
	}
	
	/**
	 * Parse the check_command string into command_name and command_arg
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @param  string checkCommand from $payload
	 * @return array  [0] => 'lan_ping', [1] => '!80!80'
	 */
	public function parseCheckCommand($checkCommand){
		$cc = explode('!', $checkCommand, 2);
		$return = [];
		if(isset($cc[0])){
			$return[0] = $cc[0];
		}else{
			$return[0] = '';
		}
		if(isset($cc[1])){
			$return[1] = $cc[1];
		}else{
			$return[1] = '';
		}
		return $return;
	}
}
