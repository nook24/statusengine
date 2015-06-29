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
class PnpController extends AppController{
	public $uses = [
		'Rrdtool',
		'Legacy.Objects',
	];
	
	public function index($serviceObjectId = null){
		if(!$this->Objects->exists($serviceObjectId)){
			throw new NotFoundException(__('Service not found'));
		}
		
		Configure::load('Interface');
		if(!file_exists(Configure::read('Interface.pnp4nagios'))){
			$this->redirect(['action' => 'whoops', $serviceObjectId]);
		}
		
		$object = $this->Objects->findByObjectId($serviceObjectId);
		
		if(!$this->Rrdtool->hasGraph($object['Objects']['name1'], $object['Objects']['name2'])){
			throw new NotFoundException(__('Graph data not found'));
		}
		
		$this->set(compact([
			'object',
		]));
	}
	
	public function whoops($serviceObjectId = null){
		Configure::load('Interface');
		$path = Configure::read('Interface.pnp4nagios');
		if(file_exists($path)){
			$this->redirect(['action' => 'index', $serviceObjectId]);
		}
		$this->set('path', $path);
	}
}
