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
class PerformanceController extends AppController{
	public $uses = false;

	public function index(){
		Configure::load('Interface');
		$naemonstats = Configure::read('Interface.naemonstats');

		$error = false;
		$output = [];
		if(!file_exists($naemonstats)){
			$error = 'File '.$naemonstats.' does not exists';
		}
		if(!is_executable($naemonstats)){
			$error = 'File '.$naemonstats.' is not executable';
		}else{
			exec($naemonstats, $output);
		}

		$this->set(compact([
			'output',
			'error'
		]));
		$this->set('_serialize', [
			'output',
			'error'
		]);
	}
}
