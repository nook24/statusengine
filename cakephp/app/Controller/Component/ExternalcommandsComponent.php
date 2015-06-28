<?php
/**
* Copyright (C) 2015 Daniel Ziegler <daniel@statusengine.org>
* 
* This file is part of Statusengine.
* 
* Statusengine is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 2 of the License, or
* (at your option) any later version.
* 
* Statusengine is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with Statusengine.  If not, see <http://www.gnu.org/licenses/>.
*/
class ExternalcommandsComponent extends Component{
	public function initialize(Controller $controller, $settings = []){
		$this->Controller = $controller;
	}
	
	public function checkCmd(){
		$commandFile = $this->Controller->Configvariable->getCommandFile();
		$commandFileError = false;
		if($commandFile === false){
			$commandFileError = 'External command file not found in database! Check your app/Config/Statusengine.php => coreconfig settings!';
		}else{
			if(!is_writable($commandFile)){
				$commandFileError = 'External command file '.$commandFile.' is not writable';
			}
			if(!file_exists($commandFile)){
				$commandFileError = 'External command file '.$commandFile.' does not exists';
			}
		}
		
		$this->Controller->set('commandFileError', $commandFileError);
		return $commandFileError;
	}
	
	public function createDowntime($type = 'host', $options = []){
		if($type == 'host'){
			$template = '%s;%s;%u;%u;%d;%d;%u;%s;%s';
			//Create host downtime
			switch($options['type']){
				case 1:
					//SCHEDULE_HOST_SVC_DOWNTIME
					array_unshift($options['parameters'], 'SCHEDULE_HOST_DOWNTIME');
					$this->write(vsprintf($template, $options['parameters']));
					
					unset($options['parameters'][0]);
					
					array_unshift($options['parameters'], 'SCHEDULE_HOST_SVC_DOWNTIME');
					$this->write(vsprintf($template, $options['parameters']));
					break;
				
				case 2:
					//SCHEDULE_AND_PROPAGATE_TRIGGERED_HOST_DOWNTIME
					array_unshift($options['parameters'], 'SCHEDULE_AND_PROPAGATE_TRIGGERED_HOST_DOWNTIME');
					$this->write(vsprintf($template, $options['parameters']));
					break;
				
				case 3:
					//SCHEDULE_AND_PROPAGATE_HOST_DOWNTIME
					array_unshift($options['parameters'], 'SCHEDULE_AND_PROPAGATE_HOST_DOWNTIME');
					$this->write(vsprintf($template, $options['parameters']));
					break;
				
				default:
					//SCHEDULE_HOST_DOWNTIME
					array_unshift($options['parameters'], 'SCHEDULE_HOST_DOWNTIME');
					$this->write(vsprintf($template, $options['parameters']));
					break;
			}
		}else{
			$template = '%s;%s;%s;%u;%u;%d;%d;%u;%s;%s';
			array_unshift($options, 'SCHEDULE_SVC_DOWNTIME');
			$this->write(vsprintf($template, $options));
		}
	}
	
	public function deleteDowntime($type, $internalDowntimeId){
		$template = '%s;%d';
		if($type == 'host'){
			$options = [
				'DEL_HOST_DOWNTIME',
				$internalDowntimeId
			];
		}else{
			$options = [
				'DEL_SVC_DOWNTIME',
				$internalDowntimeId
			];
		}
		$this->write(vsprintf($template, $options));
	}
	
	public function rescheduleService($options){
		$template = '%s;%s;%s;%u';
		array_unshift($options, 'SCHEDULE_FORCED_SVC_CHECK');
		$this->write(vsprintf($template, $options));
	}
	
	public function rescheduleHost($options){
		$template = '%s;%s;%u';
		array_unshift($options, 'SCHEDULE_FORCED_HOST_CHECK');
		$this->write(vsprintf($template, $options));
	}
	
	public function rescheduleHostAndServices($options){
		$template = '%s;%s;%u';
		array_unshift($options, 'SCHEDULE_FORCED_HOST_SVC_CHECKS');
		$this->write(vsprintf($template, $options));
	}
	
	public function write($command){
		if($this->checkCmd() === false){
			$file = $this->Controller->Configvariable->getCommandFile();
			$cmd = fopen($file, 'a+');
			fwrite($cmd, '['.time().'] '.$command.PHP_EOL);
			fclose($cmd);
			return true;
		}
		return false;
	}
}