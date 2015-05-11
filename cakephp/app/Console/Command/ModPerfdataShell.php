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
* or service checks i recived mollions of GEARMAN_UNEXPECTED_PACKET errors and my Gearman Job Server
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
	
	//Some class variables
	protected $worker = null;
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
		
		//Load CakePHP's XML class
		App::uses('Xml', 'Utility');
		
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
		
		$this->createWorker();
		$this->work();
	}
	
	public function createWorker(){
		$this->worker = new GearmanWorker();
		$this->worker->addOptions(GEARMAN_WORKER_NON_BLOCKING);
		$this->worker->addServer($this->Config['GEARMAN']['server'], $this->Config['GEARMAN']['port']);
		$this->worker->addFunction('perfdata', [$this, 'processPerfdata']);
	}
	
	public function fillKeyWithZero(){
		$key = $this->Config['MOD_GEARMAN']['key'];
		while(strlen($key) < 32){
			$key .= chr(0);
		}
		$this->Config['MOD_GEARMAN']['key'] = $key;
	}
	
	public function decrypt($stringFormModGearman){
		return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->Config['MOD_GEARMAN']['key'], base64_decode($stringFormModGearman), MCRYPT_MODE_ECB);
	}
	
	public function work(){
		while(true){
			$this->worker->work();
			sleep(5);
		}
	}
	
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
			
			$xmlAsArray = [
				'NAGIOS' => [
					'RRD' => [],
					'XML' => [
						'VERSION' => 4
					],
					'NAGIOS_DATATYPE' => $parsedPerfdataString['DATATYPE'],
					'NAGIOS_TIMET' => $parsedPerfdataString['TIMET'],
					'NAGIOS_HOSTNAME' => $parsedPerfdataString['HOSTNAME'],
					'NAGIOS_SERVICEDESC' => $parsedPerfdataString['SERVICEDESC'],
					'NAGIOS_SERVICEPERFDATA' => $parsedPerfdataString['SERVICEPERFDATA'],
					'NAGIOS_SERVICECHECKCOMMAND' => $parsedPerfdataString['SERVICECHECKCOMMAND'],
					'NAGIOS_SERVICESTATE' => $this->servicestate[$this->$parsedPerfdataString['SERVICESTATE']],
					'NAGIOS_SERVICESTATETYPE' => ($parsedPerfdataString['SERVICESTATETYPE'] == 1 ? 'HARD' : 'SOFT'),
					'NAGIOS_XMLFILE' => $this->Config['PERFDATA']['dir'].$parsedPerfdataString['HOSTNAME'].'/'.$parsedPerfdataString['SERVICEDESC'].'.xml'
				]
			];
			
			$dataSourceCounter = 1;
			foreach($parsedPerfdata as $ds => $data){
				if(is_numeric($data['current'])){
					$appendXml = [
						'DATASOURCE' => [
							'TEMPLATE' => $this->parseCheckCommand($parsedPerfdataString['SERVICECHECKCOMMAND'])[0],
							'RRDFILE' => $this->Config['PERFDATA']['dir'].$parsedPerfdataString['HOSTNAME'].'/'.$parsedPerfdataString['SERVICEDESC'].'.rrd',
							'RRD_STORAGE_TYPE' => 'SINGLE',
							'RRD_HEARTBEAT' => $this->Config['RRD']['heartbeat'],
							'IS_MULTI' => 0,
							'DS' => $dataSourceCounter,
							'NAME' => $ds,
							'LABEL' => $ds,
							'UNIT' => $data['unit'],
							'ACT' => $data['current'],
							'WARN' => $data['warning'],
							'WARN_MIN' => null,
							'WARN_MAX' => null,
							'WARN_RANGE_TYPE' => null,
							'CRIT' => $data['critical'],
							'CRIT_MIN' => null,
							'CRIT_MAX' => null,
							'CRIT_RANGE_TYPE' => null,
							'MIN' => $data['min'],
							'MAX' => $data['max']
						]
					];
					$xmlAsArray['NAGIOS']['DATASOURCES'][] = $appendXml;
					unset($appendXml);
				}
				$dataSourceCounter++;
			}
			//debug($xmlAsArray);
			
			$this->writeToRrd($xmlAsArray);
			
		}
	}
	
	public function writeToRrd($xmlAsArray){
		$this->checkAndCreateXML($xmlAsArray);
		
		$rrdOptions = [
			$xmlAsArray['NAGIOS']['NAGIOS_TIMET']
		];
		$dataSourceCounter = 1;
		foreach($xmlAsArray['NAGIOS']['DATASOURCES'] as $dataSource){
			if(file_exists($dataSoruce['DATASOURCE']['RRDFILE'])){
				//RRD File exists, we can simple fire up rrd_update
				$rrdOptions[] = $dataSoruce['DATASOURCE']['ACT'];
				$updateRrd = true;
			}else{
				//We need to create the RRD file first
				$updateRrd = false;
			}
		}
		
		if($updateRrd{
			if(!rrd_update($dataSoruce['DATASOURCE']['RRDFILE'], [implode(':', $rrdOptions)])){
				$this->out('Error on updating RRD');
				//1431375341:2182
				//1431375345:0.042000:0
				debug(rrd_error());
			};
		}else{
			//RRA:AVERAGE:0.5:1:576000 RRA:MAX:0.5:1:576000 RRA:MIN:0.5:1:576000 DS:1:GAUGE:8460:U:U --start=1431375240 --step=60
			//RRA:AVERAGE:0.5:1:576000 RRA:MAX:0.5:1:576000 RRA:MIN:0.5:1:576000 DS:1:GAUGE:8460:U:U DS:2:GAUGE:8460:U:U --start=1431375345 --step=60
			if(!rrd_create($dataSoruce['DATASOURCE']['RRDFILE'], [])){
				$this->out('Error on updating RRD');
				debug(rrd_error());
			}
		}
		
	}
	
	public function checkAndCreateXML($xmlAsArray){
		return;
		$xmlFile = new File($xmlAsArray['NAGIOS']['NAGIOS_XMLFILE']);
		if(!$xmlFile->exists()){
			$xmlFile->create();
		}
		
	}
	
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
