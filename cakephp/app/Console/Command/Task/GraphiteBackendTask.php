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
*                   Perfdata Backend Extension for Rrdtool
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
* This extension for statusengine uses the parsed performance data and
* saves performance data to graphite
* So you dont need to install any additional software to get this job done
*
**********************************************************************************/

class GraphiteBackendTask extends AppShell{

	public $Config = [];

	private $host;

	private $port;

	private $prefix;

	private $socket;

	private $lastErrNo;

	private $hostnameCache = [];

	private $servicenameCache = [];

	public function init($Config){
		$this->Config = $Config;
		$this->host = $this->Config['host'];
		$this->port = $this->Config['port'];
		$this->prefix = $this->Config['prefix'];
	}

	/**
	 * @param array $parsedPerfdata
	 * @param string $hostname
	 * @param string $servicedesc
	 * @param int $timestamp
	 */
	public function save($parsedPerfdata, $hostname, $servicedesc, $timestamp){
		if($this->connect()){
			foreach($parsedPerfdata as $ds => $_data){
				$data = $this->buildString($ds, $_data, $hostname, $servicedesc, $timestamp);
				$this->write($data);
			}
		}
		$this->disconnect();
	}

	private function write($message){
		$message .= PHP_EOL;
		$this->lastErrNo = null;
		if(!@socket_send($this->socket, $message, strlen($message), 0)){
			CakeLog::error(sprintf(
				'Graphite save error: %s %s',
				$this->getLastErrNo(),
				$this->getLastError()
			));
			return false;
		}
		return true;
	}

	private function connect(){
		$this->socket = socket_create(AF_INET, SOCK_STREAM, IPPROTO_IP);
		if(!@socket_connect($this->socket, $this->host, $this->port)){
			CakeLog::error(sprintf(
				'Graphite connection error: %s %s',
				$this->getLastErrNo(),
				$this->getLastError()
			));
			return false;
		}
		return true;
	}

	private function disconnect(){
		if(is_resource($this->socket)){
			socket_close($this->socket);
		}
		$this->socket = null;
	}

	private function getLastErrNo(){
		if(is_resource($this->socket)){
			$this->lastErrNo = socket_last_error($this->socket);
			return $this->lastErrNo;
		}
		return false;
	}

	private function getLastError(){
		return socket_strerror($this->lastErrNo);
	}

	private function buildString($datasource, $data, $hostname, $servicedesc, $timestamp){
		$datasource = $this->replaceCharacters($datasource);
		$hostname = $this->replaceCharacters($hostname);
		$servicedesc = $this->replaceCharacters($servicedesc);
		return sprintf(
			'%s.%s.%s.%s %s %s',
			$this->prefix,
			$hostname,
			$servicedesc,
			$datasource,
			$data['current'],
			$timestamp
		);
	}

	public function replaceCharacters($str){
		return preg_replace($this->Config['replace_characters'], '_', $str);
	}

	public function requireHostNameCaching(){
		if($this->Config['use_host_display_name'] === true){
			return true;
		}
		return false;
	}

	public function requireServiceNameCaching(){
		if($this->Config['use_service_display_name'] === true){
			return true;
		}
		return false;
	}

	public function requireNameCaching(){
		if($this->requireHostNameCaching() === true){
			return true;
		}

		if($this->requireServiceNameCaching() === true){
			return true;
		}

		return false;
	}

	public function addHostdisplayNameToCache($hostname, $hostdisplayname){
		$this->hostnameCache[md5($hostname)] = $hostdisplayname;
	}

	public function getHostdisplayNameFromCache($hostname){
		if(isset($this->hostnameCache[md5($hostname)])){
			return $this->hostnameCache[md5($hostname)];
		}
		return null;
	}

	public function addServicedisplayNameToCache($hostname, $servicedesc, $servicedisplayname){
		if(!isset($this->servicenameCache[md5($hostname)])){
			$this->servicenameCache[md5($hostname)] = [];
		}
		$this->servicenameCache[md5($hostname)][md5($servicedesc)] = $servicedisplayname;
	}

	public function getServicedisplayNameFromCache($hostname, $servicedesc){
		if(isset($this->servicenameCache[md5($hostname)][md5($servicedesc)])){
			return $this->servicenameCache[md5($hostname)][md5($servicedesc)];
		}
		return null;
	}

	public function clearCache(){
		$this->hostnameCache = [];
		$this->servicenameCache = [];
	}
}
