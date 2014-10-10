<?php
class Timerange extends AppModel{
	public $useTable = 'timeranges';
	public $hasAndBelongsToMany = [
		'Timeperiod' => [
			'className' => 'Timeperiod',
			'joinTable' => 'timeperiods_to_timeranges',
			'foreignKey' => 'timerange_id',
			'associationForeignKey' => 'timeperiod_id',
			'unique' => true
		],
	];
}
