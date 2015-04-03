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
*                        Memcached Extension Client Model
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
* This is the Model of Statusengin's Memcached Client
* It handels all the find, order and condition requests of
* the different object classes
*
**********************************************************************************/

namespace StatusengineMemory;
class Model{
	public $keyPrefix = '';
	public $Memcached = null;
	public $ModelName = '';
	
	public function find($objectName, $options = []){
		if(is_array($objectName)){
			return $this->findAll();
		}
		
		$result = $this->Memcached->get($this->keyPrefix.$objectName);
		if(!$result){
			return [];
		}
		
		$matchConditions = true;
		if(isset($options['conditions'])){
			foreach($options['conditions'] as $fieldName => $value){
				if(!isset($result[$fieldName]) || $result[$fieldName] != $value){
					$matchConditions = false;
					//Conditions dont match, break out of foreach to save time and go on
					break;
				}
			}
			
			if($matchConditions === false){
				return [];
			}
		}
		
		//build up cakephp's default array structure
		$result = [$this->ModelName => $result];
		
		if(isset($options['join'])){
			foreach($options['join'] as $ModelName){
				if($ModelName == 'Acknowledgement'){
					if(isset($result[$this->ModelName]['problem_has_been_acknowledged']) && $result[$this->ModelName]['problem_has_been_acknowledged'] > 0){
						$Acknowledgement = new Acknowledgement($this->Memcached);
						$_result = $Acknowledgement->find($objectName);
					}
				}
				
				if($ModelName == 'Downtime'){
					if(isset($result[$this->ModelName]['scheduled_downtime_depth']) && $result[$this->ModelName]['scheduled_downtime_depth'] > 0){
						$Downtime = new Downtime($this->Memcached);
						$_result = $Downtime->find($objectName);
					}
				}
				
				$result[$ModelName] = [];
				if(isset($_result[$ModelName])){
					$result[$ModelName] = $_result[$ModelName];
				}
			}
		}
		
		return $result;
	}
	
	public function findAll($objectNamesAsArray, $options = []){
		$return = [];
		foreach($objectNamesAsArray as $objectName){
			$result = $this->find($objectName, $options);
			if(!empty($result)){
				$return[] = $result;
			}
		}
		return $return;
	}
}
