<?php
class FilterHelper extends AppHelper{
	
	public $helpers = ['Form'];
	
	public function hosts(){
		echo $this->Form->create('Filter', ['url' => $this->params]);
		$html = '
		<div class="col-xs-12 col-md-2">
			<div class="input-group">
				<span class="input-group-addon">
					<input type="checkbox" name="data[Filter][Hoststatus][0]" id="FilterHostUp" '.$this->refill('Hoststatus', 0).'>
				</span>
				<label for="FilterHostUp" class="form-control">'.__('Up').'</label>
			</div>
		</div>
		<div class="col-xs-12 col-md-2">
			<div class="input-group">
				<span class="input-group-addon">
					<input type="checkbox" name="data[Filter][Hoststatus][1]" id="FilterHostDown" '.$this->refill('Hoststatus', 1).'>
				</span>
				<label for="FilterHostDown" class="form-control">'.__('Down').'</label>
			</div>
		</div>
		<div class="col-xs-12 col-md-2">
			<div class="input-group">
				<span class="input-group-addon">
					<input type="checkbox" name="data[Filter][Hoststatus][2]" id="FilterHostUnreachable" '.$this->refill('Hoststatus', 2).'>
				</span>
				<label for="FilterHostUnreachable" class="form-control">'.__('Unreachable').'</label>
			</div>
		</div>
		<div class="col-xs-12 col-md-6">
			<div class="input-group">
				<input type="text" name="data[Filter][Objects][name1]" value="'.$this->refill('Objects', 'name1', false).'" class="form-control" placeholder="'.__('Search...').'">
				<span class="input-group-btn">
					'.$this->Form->submit(__('Search'), ['class' => 'btn btn-default', 'style' => 'border-left: none; margin-left: -1px;']).'
				</span>
			</div>
		</div>';
		echo $html;
		echo $this->Form->end();
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