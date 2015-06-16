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
class FilterHelper extends AppHelper{
	
	public $helpers = ['Form'];
	public $_filter = [];
	
	public function beforeRender($viewFile){
		$this->View = $this->_View;
		$this->_filter = $this->_View->viewVars['FilterComponent_filter'];
	}
	
	public function render(){
		$html = '<div class="row" style="padding-bottom: 15px;">';
		$html .= $this->Form->create('Filter', ['url' => $this->params]);
		foreach($this->_filter as $modelName => $fields){
			foreach($fields as $fieldName => $fieldOptions){
				
				switch($fieldOptions['type']){
					case 'text':
						$options = [
							'class' => 'col-xs-12 col-md-6',
							'label' => __('Search...'),
						];
					
						$fieldOptions = Hash::merge($options, $fieldOptions);
					
						$html.= '
							<div class="'.$fieldOptions['class'].'">
								<div class="input-group">
									<input type="text" name="data[Filter][Objects][name1]" value="'.$this->refill('Objects', 'name1', false).'" class="form-control" placeholder="'.$fieldOptions['label'].'">';
									if($fieldOptions['submit'] == true){
										$html.= '
										<span class="input-group-btn">
											'.$this->Form->submit(__('Search'), ['class' => 'btn btn-default', 'style' => 'border-left: none; margin-left: -1px;']).'
										</span>';
									}
									$html.= '
								</div>
							</div>';
							break;
					
					case 'checkbox':
						if(!isset($fieldOptions['class'])){
							$fieldOptions['class'] = 'col-xs-12 col-md-2';
						}

						if(isset($fieldOptions['value']) && is_array($fieldOptions['value'])){
							foreach($fieldOptions['value'] as $value => $label){
								$html.= '
									<div class="'.$fieldOptions['class'].'">
										<div class="input-group">
											<span class="input-group-addon">
											<input type="hidden" name="data[Filter]['.$modelName.']['.$fieldName.']['.$value.']" value="0" />
												<input type="checkbox" name="data[Filter]['.$modelName.']['.$fieldName.']['.$value.']" id="Filter'.$modelName.$fieldName.$value.'" value="1" />
											</span>
											<label for="Filter'.$modelName.$fieldName.$value.'" class="form-control">'.h($label).'</label>
										</div>
									</div>';
							}
						}else{
							$options = [
								'class' => 'col-xs-12 col-md-2',
								'label' => $fieldName
							];
							
							$fieldOptions = Hash::merge($_options, $fieldOptions);
							
							$html.='
							<div class="'.$fieldOptions['class'].'">
								<div class="input-group">
									<span class="input-group-addon">
										<input type="hidden" name="data[Filter]['.$modelName.']['.$fieldName.']['.$value.']" value="0" />
										<input type="checkbox" name="data[Filter]['.$modelName.']['.$fieldName.']['.$value.']" id="Filter'.$modelName.$fieldName.$value.'" value="1" />
									</span>
									<label for="Filter'.$modelName.$fieldName.$value.'" class="form-control">'.h($fieldOptions['label']).'</label>
								</div>
							</div>';
						}
						break;
				}
			}
		}
		$html .= '</div>';
		return $html;
	}
	
	private function refill($Model = 'Hoststatus', $field = 0, $checked = true, $default = ''){
		if(isset($this->request->data['Filter'][$Model][$field])){
			if($checked === true){
				return 'checked="checked"';
			}
			
			return $this->request->data['Filter'][$Model][$field];
		}
		
		return $default;
	}
}