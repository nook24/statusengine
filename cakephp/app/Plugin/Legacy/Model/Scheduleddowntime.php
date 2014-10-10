<?php
class Scheduleddowntime extends LegacyAppModel{
	public $useDbConfig = 'legacy';
	public $useTable = 'scheduleddowntime';
	public $primaryKey = 'scheduleddowntime_id';


	/***
	 * will delete downtime data from the past
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 * @since 1.0.0
	 */
	public function cleanup(){
		$this->deleteAll([
			'Scheduleddowntime.scheduled_start_time < FROM_UNIXTIME('.time().')',
			'Scheduleddowntime.actual_start_time_usec <' => time()
		]);
	}
}