<?php
class Host extends LegacyAppModel{
	public $useDbConfig = 'legacy';
	public $useTable = 'hosts';
	public $primaryKey = 'host_id';
}