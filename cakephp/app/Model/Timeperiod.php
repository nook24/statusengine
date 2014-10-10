<?php
class Timeperiod extends AppModel{
	public $belongsTo = [
		'Objects' => [
			'className' => 'Objects',
			'foreignKey' => 'object_id'
		]
	];

	public $hasAndBelongsToMany = [
		'Timerange' => [
			'className' => 'Timerange',
			'joinTable' => 'timeperiods_to_timeranges',
			'foreignKey' => 'timeperiod_id',
			'associationForeignKey' => 'timerange_id',
			'unique' => true
		],
	];

}
