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
class StatusHelper extends AppHelper{
	
	public $hostClasses = [
		0 => 'success',
		1 => 'danger',
		2 => 'unknown'
	];
	
	public $hostState = [
		0 => 'Up',
		1 => 'Down',
		2 => 'Unreachable'
	];
	
	public $serviceClasses = [
		0 => 'success',
		1 => 'warning',
		2 => 'danger',
		3 => 'unknown'
	];
	
	public $serviceState = [
		0 => 'Ok',
		1 => 'Warning',
		2 => 'Critical',
		3 => 'Unknown'
	];
	
	public function hostBorder($state = 0){
		$states = [
			0 => 'host_up_border',
			1 => 'host_down_border',
			2 => 'host_unreachable_border'
		];
		
		if(!isset($states[$state])){
			return 'host_nostate_border';
		}
		
		return $states[$state];
	}
	
	public function serviceBorder($state = 0){
		$states = [
			0 => 'service_ok_border',
			1 => 'service_warnign_border',
			2 => 'service_critical_border',
			3 => 'service_unknown_border'
		];
		
		if(!isset($states[$state])){
			return 'service_nostate_border';
		}
		
		return $states[$state];
	}
	
	public function hostProgressbar($hoststatus, $hostObjectId = false){
		//Dirty workaround for HomeController
		if($hostObjectId === false){
			$hoststatus = [$hoststatus];
		}
		$html = '<div class="progress">';
		if(isset($hoststatus[$hostObjectId])){
			$count = array_sum($hoststatus[$hostObjectId]);
			foreach($hoststatus[$hostObjectId] as $state => $counter){
				$html .= '<div class="progress-bar progress-bar-'.$this->hostClasses[$state].'" role="progressbar" style="width:'.round($counter/$count*100).'%;" title="'.round($counter/$count*100).'% '.$this->hostState[$state].'"></div>';
			}
		}else{
			$html .= '<div class="progress-bar progress-bar-unknown" role="progressbar" style="width:100%;"></div>';
		}
		$html .= '</div>';
		return $html;
	}
	
	public function serviceProgressbar($servicestatus, $hostObjectId = false){
		//Dirty workaround for HomeController
		if($hostObjectId === false){
			$servicestatus = [$servicestatus];
		}
		$html = '<div class="progress">';
		if(isset($servicestatus[$hostObjectId])){
			$count = array_sum($servicestatus[$hostObjectId]);
			if($count > 0){
				foreach($servicestatus[$hostObjectId] as $state => $counter){
					$html .= '<div class="progress-bar progress-bar-'.$this->serviceClasses[$state].'" role="progressbar" style="width:'.round($counter/$count*100).'%;" title="'.round($counter/$count*100).'% '.$this->serviceState[$state].'"></div>';
				}
			}else{
				$html .= '<div class="progress-bar progress-bar-primary" role="progressbar" style="width:100%;"></div>';
			}
		}else{
			$html .= '<div class="progress-bar progress-bar-unknown" role="progressbar" style="width:100%;"></div>';
		}
		$html .= '</div>';
		return $html;
	}
	
	public function hoststatus($currentState){
		if(isset($this->hostState[$currentState])){
			return __($this->hostState[$currentState]);
		}
		return __('???');
	}
	
	public function servicestatus($currentState){
		if(isset($this->serviceState[$currentState])){
			return __($this->serviceState[$currentState]);
		}
		return __('???');
	}
	
	public function hostStateIcon($currentState){
		if(isset($this->hostClasses[$currentState]) && isset($this->hostState[$currentState])){
			return '<span class="label label-default label-'.$this->hostClasses[$currentState].'">'.__($this->hostState[$currentState]).'</span>';
		}
		return '<span class="label label-default">'.__('???').'</span>';
	}
	
	public function serviceStateIcon($currentState){
		if(isset($this->serviceClasses[$currentState]) && isset($this->serviceState[$currentState])){
			return '<span class="label label-default label-'.$this->serviceClasses[$currentState].'">'.__($this->serviceState[$currentState]).'</span>';
		}
		return '<span class="label label-default">'.__('???').'</span>';
	}
	
	public function booleanValue($value, $options = []){
		$_options = [
			'text' => [
				1 => __('Enabled'),
				0 => __('Disabled')
			]
		];
		
		$options = Hash::merge($_options, $options);
		
		if($value == 1){
			return '<span class="label label-success">'.$options['text'][1].'</span>';
		}
		return '<span class="label label-danger">'.$options['text'][0].'</span>';
	}
}
