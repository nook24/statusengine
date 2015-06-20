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
class UtilsHelper extends AppHelper{
	
	public function getObjectName($objectTypeId = 0){
		$objects = [
			 1 => __('Host'),
			 2 => __('Service'),
			 3 => __('Hostgroup'),
			 4 => __('Servicegroup'),
			 5 => __('Hostescalation'),
			 6 => __('Serviceescalation'),
			 7 => __('Hostdependency'),
			 8 => __('Servicedependency'),
			 9 => __('Timeperiod'),
			10 => __('Contact'),
			11 => __('Contactgroup'),
			12 => __('Command')
		];
		
		if(isset($objects[$objectTypeId])){
			return $objects[$objectTypeId];
		}
		
		return __('Unknown object');
	}
}
