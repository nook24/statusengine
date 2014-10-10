<?php
class Dbversion extends LegacyAppModel{
	public $useDbConfig = 'legacy';
	public $useTable = 'dbversion';
	public $primaryKey = 'name';
}