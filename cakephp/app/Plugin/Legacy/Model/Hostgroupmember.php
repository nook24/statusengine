<?php
class Hostgroupmember extends LegacyAppModel{
	public $useDbConfig = 'legacy';
	public $useTable = 'hostgroup_members';
	public $primaryKey = 'hostgroup_member_id';
}