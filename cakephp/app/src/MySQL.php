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
**********************************************************************************/

class MySQLBulk{
	
	/**
	 * @var mysqli
	 */
	private $mysqli;
	
	/**
	 * @var string
	 */
	private $host;
	
	/**
	 * @var string
	 */
	private $username;
	
	/**
	 * @var string
	 */
	private $password;
	
	/**
	 * @var int
	 */
	private $port;
	
	/**
	 * @var string
	 */
	private $database;
	
	/**
	 * @param CakePHP datasource
	 */
	public function __construct($datasource){
		$this->username = $datasource->config['login'];
		$this->password = $datasource->config['password'];
		$this->port = $datasource->config['port'];
		$this->database = $datasource->config['database'];
		$this->host = $datasource->config['host'];
	}
	
	public function connect(){
		$this->mysqli = new mysqli(sprintf('%s:%s', $this->host, $this->port), $this->username, $this->password, $this->database);
	}
	
	public function query($query){
		$this->mysqli->query($query);
	}
	
}