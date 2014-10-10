<?php
class Contact extends AppModel{
	public $belongsTo = [
		'Objects' => [
			'className' => 'Objects',
			'foreignKey' => 'object_id'
		]
	];
	
	public $hasAndBelongsToMany = [
		'Contactgroup' => [
			'className' => 'Contactgroup',
			'joinTable' => 'contacts_to_contactgroups',
			'foreignKey' => 'contact_id',
			'associationForeignKey' => 'contactgroup_id',
		],
	];
	
}
