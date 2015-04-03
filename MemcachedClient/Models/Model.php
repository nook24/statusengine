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
* The MIT License (MIT)
* 
* Copyright (c) <2015> <Daniel Ziegler>
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
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
		
		return [$this->ModelName => $result];
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
