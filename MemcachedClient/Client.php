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
*                           Memcached Extension Client
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
*
* With Statusengine Memcached Client you can acces status data stored in the
* memory based database provided by memcached
*
*
**********************************************************************************/

namespace StatusengineMemory;

class StatusengineMemoryClient{
	protected $__server = '127.0.0.1';
	protected $__port = 11211;
	protected $Memcached = null;
	
	public function __construct($server, $port){
		require_once 'Models/Model.php';
		require_once 'Models/Hoststatus.php';
		require_once 'Models/Servicestatus.php';
		require_once 'Models/Acknowledgement.php';
		require_once 'Models/Downtime.php';
		
		$this->StatusengineMemoryClient($server, $port);
	}
	
	public function StatusengineMemoryClient($server, $port){
		$this->__server        = $server;
		$this->__port          = $port;
		$this->Memcached       = new \Memcached;
		$this->Hoststatus      = new Hoststatus($this->Memcached);
		$this->Servicestatus   = new Servicestatus($this->Memcached);
		return $this->Memcached->addServer($server, $port);
	}
	
}
