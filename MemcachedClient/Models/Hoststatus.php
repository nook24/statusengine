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

class Hoststatus extends Model{
	public $keyPrefix = 'hs_';
	public $ModelName = 'Hoststatus';
	
	public function __construct($Memcached){
		$this->Memcached = $Memcached;
	}
	
	public function find($hostName = '', $options = []){
		return parent::_find($this->serialize($hostName), $options);
	}
	
	/*
	findAll() usage:
	$hostNames = [
		'localhost',
		'router'
	]
	*/
	public function findAll($hostNames = [], $options = []){
		if(!empty($hostNames)){
			return parent::_findAll($this->serialize($hostNames), $options);
		}else{
			return parent::_findAll($this->getAllKeys(), $options);
		}
	}
	
	public function serialize($hostNames){
		if(is_array($hostNames)){
			$return = [];
			foreach($hostNames as $hostName){
				$return[] = md5($hostName);
			}
			return $return;
		}
		
		return md5($hostNames);
	}
}
