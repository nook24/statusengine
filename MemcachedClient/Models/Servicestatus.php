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
}
