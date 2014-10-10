<?php
class Flapping extends LegacyAppModel{
	public $useDbConfig = 'legacy';
	public $useTable = 'flappinghistory';
	public $primaryKey = 'flappinghistory_id';
}