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

class BulkRepository{
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
	 * @var int
	 */
	private $queryTime;

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

	public function __construct(Model $Model, $queryLimit = 200, $queryTime = 10){
		$this->Model = $Model;
		$this->queryLimit = $queryLimit;
		$this->queryTime = $queryTime;

		$this->lastPush = time();

		$this->cacheSchema();
		$this->db = $this->Model->getDataSource();
	}

	public function commit($data){
		$this->cache[] = $data;
		$this->counter++;

		if($this->isPushRequired()){
			debug('commit -> push cache');
			debug($this->cache);
			$this->push();
		}
	}

	public function push(){

		if($this->counter > 1){
			CakeLog::debug(sprintf('Push bulk %d bulk inserts for %s', $this->counter, get_class($this->Model)));
			
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
			// TODO: fix building of recordValues. values must be in the same order as schema
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
		$trys = 20;
		for ($i = 0 ; $i < $trys ; $i++) {
			try{
				if($this->Model->useTable == 'services'){
					debug($query);
				}
				$this->db->rawQuery($query);
				if ($i > 0)
					CakeLog::info(sprintf('Solved MySQL Deadlock on %s (try %d/%d)', get_class($this->Model), $i+1, $trys));
				// we're done, exit here
				return;
			} catch(PDOException $e){
				// deadlock --> retry
				if($i < $trys && $e->errorInfo[0] == 40001 && $e->errorInfo[1] == 1213) {
					$sleep = 50000 + rand(0,450000);
					CakeLog::info('Encountered MySQL Deadlock during transaction on '.get_class($this->Model).'. Retry Command in '.floor($sleep/1000).'ms (try '.($i+1).'/'.$trys.')');
					usleep($sleep);
				}

				// too many dealocks
				elseif($e->errorInfo[0] == 40001 && $e->errorInfo[1] == 1213) {
					CakeLog::info("Couldn't solve deadlock for ".get_class($this->Modul).". Ignore for now to prevent crash: Exception: $e");
				}

				// everything else forward error
				else {
					CakeLog::info("SQL ERROR - QUERY: ".$query);
					throw $e;
				}
			}
		}
	}

	/**
	 * @return bool
	 */
	public function isPushRequired(){
		if($this->counter >= $this->queryLimit){
			return true;
		}

		if($this->lastPush < (time() - $this->queryTime)){
			return true;
		}

		return false;
	}

}
