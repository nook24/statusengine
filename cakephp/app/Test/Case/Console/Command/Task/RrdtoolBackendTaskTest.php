<?php
class RrdToolBackendTaskTest extends CakeTestCase {

	// run test in bash
	// cd /opt/statusengine/cakephp/app/Console/
	// while true; do clear;./cake test app Console/Command/Task/RrdtoolBackendTask; sleep 5; done

	public function setUp(){
		$taskCollection = new TaskCollection(new Shell());
		$this->RrdtoolBackend =  $taskCollection->load('RrdtoolBackend');

		Configure::load('Perfdata');
		$this->Config = Configure::read('perfdata');

		//Load CakePHP's File class
		App::uses('File', 'Utility');

		$this->RrdtoolBackend->init($this->Config);
	}

	public function testParseCheckCommandPing(){
		$checkCommand = 'lan_ping!80!90';
		$result = $this->RrdtoolBackend->parseCheckCommand($checkCommand);
		$expected = [
			'lan_ping',
			'80!90'
		];
		$this->assertEquals($expected, $result);
	}

	public function testParseCheckCommandOneArg(){
		$checkCommand = 'check_args!$ARG1$';
		$result = $this->RrdtoolBackend->parseCheckCommand($checkCommand);
		$expected = [
			'check_args',
			'$ARG1$'
		];
		$this->assertEquals($expected, $result);
	}

	public function testParseCheckCommandNoArgs(){
		$checkCommand = 'check_foo';
		$result = $this->RrdtoolBackend->parseCheckCommand($checkCommand);
		$expected = [
			'check_foo',
			null
		];
		$this->assertEquals($expected, $result);
	}

	public function testThresholdsDefaultWarning(){
		$thresholds = '80';
		$result = $this->RrdtoolBackend->thresholds($thresholds, 'warn');
		$expected = [
			'warn' => '80',
			'warn_min' => null,
			'warn_max' => null
		];
		$this->assertEquals($expected, $result);
	}

	public function testThresholdsDefaultCritical(){
		$thresholds = '90';
		$result = $this->RrdtoolBackend->thresholds($thresholds, 'crit');
		$expected = [
			'crit' => '90',
			'crit_min' => null,
			'crit_max' => null
		];
		$this->assertEquals($expected, $result);
	}

	public function testThresholdsMinMaxRangeWarning(){
		$thresholds = '20:60';
		$result = $this->RrdtoolBackend->thresholds($thresholds, 'warn');
		$expected = [
			'warn' => null,
			'warn_min' => '20',
			'warn_max' => '60'
		];
		$this->assertEquals($expected, $result);
	}

	public function testThresholdsMinMaxRangeCritical(){
		$thresholds = '10:90';
		$result = $this->RrdtoolBackend->thresholds($thresholds, 'crit');
		$expected = [
			'crit' => null,
			'crit_min' => '10',
			'crit_max' => '90'
		];
		$this->assertEquals($expected, $result);
	}

	public function testThresholdsOnlyMinWarning(){
		$thresholds = '20:';
		$result = $this->RrdtoolBackend->thresholds($thresholds, 'warn');
		$expected = [
			'warn' => null,
			'warn_min' => '20',
			'warn_max' => null
		];
		$this->assertEquals($expected, $result);
	}

	public function testThresholdsOnlyMinCritical(){
		$thresholds = '10:';
		$result = $this->RrdtoolBackend->thresholds($thresholds, 'crit');
		$expected = [
			'crit' => null,
			'crit_min' => '10',
			'crit_max' => null
		];
		$this->assertEquals($expected, $result);
	}

	public function testThresholdsOnlyMaxWarning(){
		$thresholds = ':60';
		$result = $this->RrdtoolBackend->thresholds($thresholds, 'warn');
		$expected = [
			'warn' => null,
			'warn_min' => null,
			'warn_max' => '60'
		];
		$this->assertEquals($expected, $result);
	}

	public function testThresholdsOnlyMaxCritical(){
		$thresholds = ':90';
		$result = $this->RrdtoolBackend->thresholds($thresholds, 'crit');
		$expected = [
			'crit' => null,
			'crit_min' => null,
			'crit_max' => '90'
		];
		$this->assertEquals($expected, $result);
	}

}
