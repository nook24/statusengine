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
*/
class Rrdtool extends AppModel{
	public $useTable = false;
	public $path = null;
	public $regEx = null;
	
	public function hasGraph($hostName, $serviceName){
		if($this->rrdExists($hostName, $serviceName)){
			if($this->xmlExists($hostName, $serviceName)){
				return true;
			}
		}
		return false;
	}
	
	public function rrdExists($hostName, $serviceName){
		if(!isset($this->path)){
			Configure::load('Perfdata');
			$this->path = Configure::read('perfdata.PERFDATA.dir');
		}
		$fileBase = $this->path . $this->replace($hostName) . DS . $this->replace($serviceName);
		if(file_exists($fileBase.'.rrd')){
			return true;
		}
		return false;
	}
	
	public function xmlExists($hostName, $serviceName){
		if(!isset($this->path)){
			Configure::load('Perfdata');
			$this->path = Configure::read('perfdata.PERFDATA.dir');
		}
		$fileBase = $this->path . $this->replace($hostName) . DS . $this->replace($serviceName);
		if(file_exists($fileBase.'.xml')){
			return true;
		}
		return false;
	}
	
	public function getPath($hostName, $serviceName){
		if(!isset($this->path)){
			Configure::load('Perfdata');
			$this->path = Configure::read('perfdata.PERFDATA.dir');
		}
		return $this->path . $this->replace($hostName) . DS . $this->replace($serviceName).'.rrd';
	}
	
	public function parseXml($hostName, $serviceName){
		if(!isset($this->path)){
			Configure::load('Perfdata');
			$this->path = Configure::read('perfdata.PERFDATA.dir');
		}
		$xmlFile = $this->path . $this->replace($hostName) . DS . $this->replace($serviceName).'.xml';
		$xml = simplexml_load_file($xmlFile);
		$return = [];
		$default = [
			'name' => null,
			'label' => null,
			'unit' => null,
			'ds' => 0
		];
		$xmlAsArray = Xml::toArray($xml);
		if(isset($xmlAsArray['NAGIOS']['DATASOURCE'])){
			foreach($xmlAsArray['NAGIOS']['DATASOURCE'] as $datasource){
				$unit = $datasource['UNIT'];
				if($unit == '%%'){
					$unit = '%';
				}
				$return[$datasource['DS']] = Hash::merge($default, [
					'name' => $datasource['NAME'],
					'label' => $datasource['LABEL'],
					'unit' => $unit,
					'ds' => $datasource['DS']
				]);
			}
		}
		return $return;
	}
	
	public function replace($string){
		if(!isset($this->regEx)){
			Configure::load('Perfdata');
			$this->regEx = Configure::read('perfdata.replace_characters');
		}
		return preg_replace($this->regEx, '_', $string);
	}
}
