<?php
class Host extends AppModel{
	public $belongsTo = [
		'Objects' => [
			'className' => 'Objects',
			'foreignKey' => 'object_id'
		]
	];
}
