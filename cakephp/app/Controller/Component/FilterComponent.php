<?php
class FilterComponent extends Component{
	
	protected $request = [];
	
	public function setRequest($request){
		$this->request = $request;
	}
	
	public function hosts(){
		$conditions = [];
		if(isset($this->request->data['Filter']['Hoststatus']) && is_array($this->request->data['Filter']['Hoststatus'])){
			$conditions['Hoststatus.current_state'] = array_keys($this->request->data['Filter']['Hoststatus']);
			
			//The user selected all host state types (0,1,2)
			//So we unset the filter, because its useless
			if(sizeof($conditions['Hoststatus.current_state']) == 3){
				unset($conditions['Hoststatus.current_state']);
			}
		}

		if(isset($this->request->data['Filter']['Objects']['name1'])){
			if($this->request->data['Filter']['Objects']['name1'] != ''){
				$conditions['Objects.name1 LIKE'] = '%'.$this->request->data['Filter']['Objects']['name1'].'%';
			}
		}
		
		return $conditions;
	}
}