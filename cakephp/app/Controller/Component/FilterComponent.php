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
class FilterComponent extends Component{

	public $isFilter = false;
	protected $request = [];
	private $_filter   = [];

	public function initialize(Controller $controller, $settings = []) {
		/*$this->request = $controller->request;
		$this->Controller = $controller;
		if(property_exists($this->Controller, 'filter')){
			$this->_filter = $this->Controller->filter;
		}*/
	}

	public function startup(Controller $controller){
		$this->Controller = $controller;
		$this->request = $controller->request;

		$this->isFilter = false;

		if(property_exists($this->Controller, 'filter')){
			$this->_filter = $this->Controller->filter;
		}

		if(!isset($this->_filter[$this->Controller->action])){
			$this->Controller->set('FilterComponent_filter', []);
			$this->Controller->set('FilterComponent_isFilter', $this->isFilter);
			return;
		}

		//Set filter settings for view
		if(isset($this->request->data['Filter'])){
			$this->isFilter = true;
		}
		$filter = $this->_filter[$this->Controller->action];
		$this->Controller->set('FilterComponent_filter', $filter);

		if(isset($this->request->data['Filter'])){
			$url = [];
			$conditions = [];
			foreach($this->request->data['Filter'] as $modelName => $field){
				foreach($field as $fieldName => $value){
					if(isset($filter[$modelName][$fieldName]['type'])){
						switch($filter[$modelName][$fieldName]['type']){
							case 'checkbox':
								if(is_array($value)){
									//We have an array of checkboxes, for status for example
									foreach($value as $key => $value){
										if($value == 1){
											$conditions[$modelName.'.'.$fieldName][] = $key;
											$url['Filter'][$modelName][$fieldName][$key] = $key;
										}
									}
								}else{
									if($value > 0){
										$conditions[$modelName.'.'.$fieldName] = $value;
										$url['Filter'][$modelName][$fieldName] = $value;
									}
								}
								break;

							case 'text':
								if($value != ''){
									$conditions[$modelName.'.'.$fieldName. ' LIKE'] = '%'.$value.'%';
									$url['Filter'][$modelName][$fieldName] = $value;
								}
								break;
						}
					}
				}
			}

			//Keep url parameters
			if(isset($this->Controller->request->params['pass']) && !empty($this->Controller->request->params['pass'])){
				$url = Hash::merge($url, $this->Controller->request->params['pass']);
			}

			if(isset($this->Controller->request->params['named']) && !empty($this->Controller->request->params['named'])){
				$named = [];
				foreach($this->Controller->request->params['named'] as $key => $param){

					//Ignore old Filter settings from URL
					if($key != 'page' && $key !== 'Filter'){
						$named[$key] = $param;
					}
				}
				$url = Hash::merge($url, $named);
			}

			//Set conditions for paginator
			$this->Controller->paginate = Hash::merge($this->Controller->paginate, array(
				'conditions' => $conditions
			));
			$this->Controller->redirect($url);
		}else{
			if(isset($this->request->params['named']['Filter']) && !empty($this->request->params['named']['Filter'])){
				$this->isFilter = true;
				foreach($this->request->params['named']['Filter'] as $modelName => $field){
					foreach($field as $fieldName => $value){
						if(isset($filter[$modelName][$fieldName]['type'])){
							switch($filter[$modelName][$fieldName]['type']){
								case 'checkbox':
									if(is_array($value)){
										//We have an array of checkboxes, for status for example
										foreach($value as $crap => $value){
											//if($value > 0){
											$conditions[$modelName.'.'.$fieldName][] = $value;
											//}
										}
									}else{
										if($value > 0){
											$conditions[$modelName.'.'.$fieldName] = $value;
										}
									}
									break;

								case 'text':
									if($value != ''){
										$conditions[$modelName.'.'.$fieldName. ' LIKE'] = '%'.$value.'%';
									}
									break;
							}
						}
					}
				}
				//Set conditions for paginator
				if(isset($conditions)){
					$this->Controller->paginate = Hash::merge($this->Controller->paginate, [
						'conditions' => $conditions
					]);
				}
			}
		}
		$this->Controller->set('FilterComponent_isFilter', $this->isFilter);
	}
}
