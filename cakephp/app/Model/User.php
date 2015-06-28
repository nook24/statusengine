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
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
class User extends AppModel {
	public $useDbConfig = 'default';
	public $validate = [
		'username' => [
			'required' => [
				'rule' => ['notEmpty'],
				'message' => 'A username is required'
			],
			'unique' => [
				'rule' => 'isUnique',
				'required' => 'create',
				'message' => 'This username is already taken'
			],
		],
		'password' => [
			'required' => [
				'rule' => ['notEmpty'],
				'message' => 'A password is required'
			]
		]
	];
	
	public function beforeSave($options = array()) {
		if(isset($this->data[$this->alias]['password'])){
			$passwordHasher = new BlowfishPasswordHasher();
			$this->data[$this->alias]['password'] = $passwordHasher->hash(
				$this->data[$this->alias]['password']
			);
		}
		return true;
	}
}
