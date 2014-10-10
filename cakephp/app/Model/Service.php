<?php
class Service extends AppModel{
	public $belongsTo = [
		'Objects' => [
			'className' => 'Objects',
			'foreignKey' => 'object_id'
		]
	];
}
