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
			return $this->findAll($objectName);
		}
		
		$result = $this->Memcached->get($this->keyPrefix.$objectName);
		if(!$result){
			return [];
		}
		
		$matchConditions = true;
		if(isset($options['conditions'])){
			foreach($options['conditions'] as $fieldName => $value){
				$fieldAndModel = $this->_SplitModelAndField($fieldName);
				if(!isset($result[$fieldAndModel['fieldName']]) || $result[$fieldAndModel['fieldName']] != $value){
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
						unset($Acknowledgement);
					}
				}
				
				if($ModelName == 'Downtime'){
					if(isset($result[$this->ModelName]['scheduled_downtime_depth']) && $result[$this->ModelName]['scheduled_downtime_depth'] > 0){
						$Downtime = new Downtime($this->Memcached);
						$_result = $Downtime->find($objectName);
						unset($Downtime);
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
	
	public function findAll($objectNamesAsArray, $options = [], $addMissingOrderResults = true){
		$return = [];
		foreach($objectNamesAsArray as $objectName){
			$result = $this->find($objectName, $options);
			if(!empty($result)){
				$return[] = $result;
			}
		}
		
		if(isset($options['order'])){
			foreach($options['order'] as $fieldName => $direction){
				$direction = strtolower($direction);
				if(in_array($direction, ['asc', 'desc'])){
					//Split Model And field (Model.field)
					$fieldAndModel = $this->_SplitModelAndField($fieldName);
					$_fieldsToOrder = [];
					$unsortedKeys = [];
					//$key is the array index 0,1,2,n $record is an aray ['Servicestatus'] => $data
					foreach($return as $key => $record){
						foreach($record as $_ModelName => $data){
							//Is this the model we want to sort, and does the field exists?
							if($_ModelName == $fieldAndModel['modelName']){
								if(isset($data[$fieldAndModel['fieldName']])){
									$_fieldsToOrder[$key] = $data[$fieldAndModel['fieldName']];
								}else{
									//The feild is missing in result but we want to add this record
									// to our return result later, so we need the key
									$unsortedKeys[] = $key;
								}
							}
						}
					}
					if($direction == 'asc'){
						asort($_fieldsToOrder);
					}
					
					if($direction == 'desc'){
						arsort($_fieldsToOrder);
					}
					
					//push the new order to return array
					$_return = [];
					foreach($_fieldsToOrder as $key => $value){
						$_return[] = $return[$key];
					}
					
					if($addMissingOrderResults === true){
						//add missing keys to result
						foreach($unsortedKeys as $key){
							$_return[] = $return[$key];
						}
					}
					
					$return = $_return;
					unset($_return);
				}
			}
		}
		return $return;
	}
	
	private function _SplitModelAndField($value = ''){
		$split = explode('.', $value, 2);
		if(sizeof($split) == 2){
			return [
				'fieldName' => $split[1],
				'modelName' => $split[0]
			];
		}
		
		return [
			'fieldName' => $split[0],
			'modelName' => $this->ModelName
		];
	}
}
