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
class SearchController extends AppController{
	public function index(){
		$this->autoRender = false;
		$query = $this->request->data('query');

		//url we need for FilterComponent: Filter[Objects][name1]:asdasd
		$this->redirect([
			'controller' => 'Hosts',
			'action' => 'index',

			'Filter' => [
				'Objects' => [
					'name1' => $query
				]
			]
		]);
	}
}
