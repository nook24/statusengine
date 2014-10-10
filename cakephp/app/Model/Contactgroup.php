<?php
class Contactgroup extends AppModel{
	public $belongsTo = [
		'Objects' => [
			'className' => 'Objects',
			'foreignKey' => 'object_id'
		]
	];
}

