<?php
class Processdata extends LegacyAppModel{
	public $useDbConfig = 'legacy';
	public $useTable = 'processevents';
	public $primaryKey = 'processevent_id';
}
