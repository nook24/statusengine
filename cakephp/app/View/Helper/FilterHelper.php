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
	private $_filter = [];
	private $_isFilter = false;

	public function beforeRender($viewFile){
		$this->View = $this->_View;
		$this->_filter = $this->_View->viewVars['FilterComponent_filter'];
		$this->_isFilter = $this->_View->viewVars['FilterComponent_isFilter'];
	}

	public function render($options = []){
		if(empty($this->_filter)){
			return;
		}

		$_options = [
			'class' => 'col-xs-12',
			'wrapRow' => true,
			'wrapStyle' => 'padding-bottom: 15px;',
		];

		$renderOptions = Hash::merge($_options, $options);

		if($renderOptions['wrapRow'] == true){
			$html = '<div class="row" style="'.$renderOptions['wrapStyle'].'">';
		}else{
			$html = '';
		}

		if($renderOptions['wrapRow'] == true){
			$html .= '<div class="'.$renderOptions['class'].'">';
		}else{
			$html .= '<div class="'.$renderOptions['class'].'" style="'.$renderOptions['wrapStyle'].'">';
		}
		if($this->_isFilter === true){
			$id = null;
			if(isset($this->params['pass'][0])){
				$id = $this->params['pass'][0];
			}
			$html .= '<a href="'.Router::url(['controller' => $this->params['controller'], 'action' => $this->params['action'], 'plugin' => $this->params['plugin'], $id]).'" class="btn btn-danger pull-right" style="margin-right: 15px;"><i class="fa fa-times"></i> '.__('Reset filter').'</a>';
		}
		$html .= '<a href="javascript:void(0);" id="openFilter" class="btn btn-default pull-right" style="margin-right: 15px;"><i class="fa fa-search"></i> '.__('Search').'</a>';
		$html .= '</div>';
		$html .= '<div class="col-xs-12" id="filterInputs" style="display:none; padding-top: 10px;">';
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

						$value = '';
						if(isset($this->request['named']['Filter'][$modelName][$fieldName])){
							$value = $this->request['named']['Filter'][$modelName][$fieldName];
						}

						$html.= '<div class="'.$fieldOptions['class'].'">';
						if($fieldOptions['submit'] == true){
							$html .= '<div class="input-group">';
						}
								$html.='<input type="text" name="data[Filter]['.$modelName.']['.$fieldName.']" value="'.$value.'" class="form-control" placeholder="'.$fieldOptions['label'].'">';
									if($fieldOptions['submit'] == true){
										$html.= '
										<span class="input-group-btn">
											'.$this->Form->submit(__('Search'), ['class' => 'btn btn-default', 'style' => 'border-left: none; margin-left: -1px;']).'
										</span>';
									}
							if($fieldOptions['submit'] == true){
								$html.='</div>';
							}
							$html.='</div>';
							break;

					case 'checkbox':
						if(!isset($fieldOptions['class'])){
							$fieldOptions['class'] = 'col-xs-12 col-md-2';
						}

						if(isset($fieldOptions['value']) && is_array($fieldOptions['value'])){
							foreach($fieldOptions['value'] as $value => $label){
								$checked = '';
								if(isset($this->request['named']['Filter'][$modelName][$fieldName][$value])){
									$checked = 'checked="checked"';
								}
								$html.= '
									<div class="'.$fieldOptions['class'].'">
										<div class="input-group">
											<span class="input-group-addon">
											<input type="hidden" name="data[Filter]['.$modelName.']['.$fieldName.']['.$value.']" value="0" />
												<input type="checkbox" name="data[Filter]['.$modelName.']['.$fieldName.']['.$value.']" id="Filter'.$modelName.$fieldName.$value.'" value="1" '.$checked.' />
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
							$checked = '';
							if(isset($this->request['named']['Filter'][$modelName][$fieldName][$value])){
								$checked = 'checked="checked"';
							}

							$html.='
							<div class="'.$fieldOptions['class'].'">
								<div class="input-group">
									<span class="input-group-addon">
										<input type="hidden" name="data[Filter]['.$modelName.']['.$fieldName.']['.$value.']" value="0" />
										<input type="checkbox" name="data[Filter]['.$modelName.']['.$fieldName.']['.$value.']" id="Filter'.$modelName.$fieldName.$value.'" value="1" '.$checked.' />
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
		if($renderOptions['wrapRow'] == true){
			$html .= '</div>';
		}
		return $html;
	}
}
