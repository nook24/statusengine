<?php
class Objects extends AppModel{
	var $actsAs = array('Containable');
	/*public $hasMany = [
		'Command' => [
			'className' => 'Command',
			'foreignKey' => 'object_id',
			'dependent' => true
		],
	];*/
	public $hasOne = [
			'Command' => [
				'className' => 'Command',
				'foreignKey' => 'object_id',
				'dependent' => true
			],
			'Contact' => [
				'className' => 'Contact',
				'foreignKey' => 'object_id',
				'dependent' => true
			],
			'Contactgroup' => [
				'className' => 'Contactgroup',
				'foreignKey' => 'object_id',
				'dependent' => true
			],
			'Timeperiod' => [
				'className' => 'Timeperiod',
				'foreignKey' => 'object_id',
				'dependent' => true
			],
			'Host' => [
				'className' => 'Host',
				'foreignKey' => 'object_id',
				'dependent' => true
			],
			'Service' => [
				'className' => 'Service',
				'foreignKey' => 'object_id',
				'dependent' => true
			]
		];
}