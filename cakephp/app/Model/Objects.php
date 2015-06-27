<?php
/**********************************************************************************
*
*    #####
*   #     # #####   ##   ##### #    #  ####  ###### #    #  ####  # #    # ######
*   #         #    #  #    #   #    # #      #      ##   # #    # # ##   # #
*    #####    #   #    #   #   #    #  ####  #####  # #  # #      # # #  # #####
*         #   #   ######   #   #    #      # #      #  # # #  ### # #  # # #
*   #     #   #   #    #   #   #    # #    # #      #   ## #    # # #   ## #
*    #####    #   #    #   #    ####   ####  ###### #    #  ####  # #    # ######
*
*                            the missing event broker
*
* --------------------------------------------------------------------------------
*
* Copyright (c) 2014 - present Daniel Ziegler <daniel@statusengine.org>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation in version 2
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*
* --------------------------------------------------------------------------------
*/
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
