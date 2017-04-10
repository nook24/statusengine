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

class StatusRepository{
	/**
	 * @var int
	 */
	private $lastPush;
	
	/**
	 * @var array
	 */
	private $cache = [];
	
	/**
	 * @var int
	 */
	private $counter = 0;
	
	/**
	 * @var int
	 */
	private $queryLimit;
	
	/**
	 * @var array
	 */
	private $schema;
	
	/**
	 * @var string
	 */
	private $baseQuery = 'INSERT INTO `%s%s` (%s) VALUES %s ON DUPLICATE KEY UPDATE %s;';
	
	/**
	 * @var DataSource
	 */
	private $db;
	
	/**
	 * @var Model
	 */
	private $Model;
	
	/**
	 * @var MySQLBulk
	 */
	private $MySQL;
	
	public function __construct(Model $Model, $queryLimit, MySQLBulk $MySQL){
		$this->Model = $Model;
		$this->queryLimit = $queryLimit;
		$this->MySQL = $MySQL;
		
		$this->lastPush = time();
		
		$this->cacheSchema();
		$this->db = $this->Model->getDataSource();
	}
	
	public function commit($data){
		$this->cache[] = $data;
		$this->counter++;
		
		if($this->isPushRequired()){
			$this->push();
		}
	}
	
	public function push(){
		
		if($this->counter > 1){
			$query = $this->buildQuery();
			$this->save($query);
		}

		$this->counter = 0;
		$this->lastPush = time();
		$this->cache = [];
	}
	
	public function pushIfRequired(){
		if($this->isPushRequired()){
			$this->push();
		}
	}
	
	private function cacheSchema(){
		$schema = $this->Model->schema();
		if(isset($schema['id'])){
			unset($schema['id']);
		}
		
		if(isset($schema['hoststatus_id'])){
			unset($schema['hoststatus_id']);
		}
		
		if(isset($schema['hostcheck_id'])){
			unset($schema['hostcheck_id']);
		}
		
		if(isset($schema['servicestatus_id'])){
			unset($schema['servicestatus_id']);
		}
		
		if(isset($schema['servicecheck_id'])){
			unset($schema['servicecheck_id']);
		}
		
		$this->schema = $schema;
	}
	
	/**
	 * @return string
	 */
	public function buildQuery(){
		$fields = [];
		foreach($this->schema as $columnName => $metaData){
			$fields[] = sprintf('`%s`', $columnName);
		}
		
		$values = [];
		foreach($this->cache as $record){
			$recordValues = [];
			foreach($record as $key => $value){
				$recordValues[] = $this->db->value($value, 'string');
			}
			$values[] = sprintf('( %s )', implode(', ', $recordValues));
		}
		
		$values = implode(', ', $values);
		
		$onDuplicate = [];
		foreach($this->schema as $columnName => $metaData){
			$onDuplicate[] = sprintf('`%s`=VALUES(`%s`)', $columnName, $columnName);
		}
		$onDuplicate = implode(', ', $onDuplicate);
		$query = sprintf(
			$this->baseQuery,
			$this->Model->tablePrefix,
			$this->Model->table,
			implode(',', $fields),
			$values,
			$onDuplicate
		);
		
		return $query;
	}
	
	/**
	 * @param string $query
	 */
	public function save($query){
		$this->MySQL->query($query);
	}
	
	/**
	 * @return bool
	 */
	public function isPushRequired(){
		if($this->counter >= $this->queryLimit){
			return true;
		}
		
		if($this->lastPush < time() - 10){
			return true;
		}
		
		return false;
	}
	
	public function flush(){
		if(!empty($this->cache)){
			
		}
		$this->lastFlush = time();
	}
}
