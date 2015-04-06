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
*                       Memcached Extension Client Hoststatus
*
* --------------------------------------------------------------------------------
*
* Statusengine Memcached Extension
* http://statusengine.org
* 
* Copyright 2015: Daniel Ziegler <daniel@statusengine.org>
* Dual licensed under the MIT or GPL Version 2 licenses.
*
* --------------------------------------------------------------------------------
*/

namespace StatusengineMemory;

class Servicestatus extends Model{
	public $keyPrefix = 'ss_';
	public $ModelName = 'Servicestatus';
	
	public function __construct($Memcached){
		$this->Memcached = $Memcached;
	}
	
	public function find($hostName, $serviceDescription, $options = []){
		return parent::_find(md5($hostName.$serviceDescription), $options);
	}
	
	/*
	findAll() usage:
	$HostAndServiceDescription = [
		'localhost' => [
			'ping',
			'disk-c',
			'memory usage'
		],
		'router' => [
			'if01'
			'if02'
			'ifn'
		]
	]
	*/
	public function findAll($HostAndServiceDescription = [], $options = []){
		if(!empty($HostAndServiceDescription)){
			$_HostAndServiceDescription = [];
			foreach($HostAndServiceDescription as $hostName => $servicesAsArray){
				foreach($servicesAsArray as $service){
					$_HostAndServiceDescription[] = $this->serialize($hostName, $service);
				}
			}
			return parent::_findAll($_HostAndServiceDescription, $options);
		}else{
			return parent::_findAll($this->getAllKeys(), $options);
		}
	}
	
	public function serialize($hostName, $serviceName){
		return md5($hostName.$serviceName);
	}
}
