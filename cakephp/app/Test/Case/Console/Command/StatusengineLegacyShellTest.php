<?php
require_once APP . 'Console' . DS . 'Command' . DS . 'StatusengineLegacyShell.php';

class StatusengineLegacyShellTest extends CakeTestCase {

	// run test in bash
	// cd /opt/statusengine/cakephp/app/Console/
	// while true; do clear;./cake test app Console/Command/StatusengineLegacyShell; sleep 5; done

	public function setUp(){
		//$taskCollection = new TaskCollection(new Shell());
		//$this->StatusengineLegacy =  new Shell('StatusengineLegacy');
		$this->StatusengineLegacyShell = new StatusengineLegacyShell();
	}

	public function testParseCheckCommandPing(){
		$checkCommand = 'lan_ping!80!90';
		$result = $this->StatusengineLegacyShell->parseCheckCommand($checkCommand);
		$expected = [
			'lan_ping',
			'80!90'
		];
		$this->assertEquals($expected, $result);
	}

	public function testParseCheckCommandOneArg(){
		$checkCommand = 'check_args!$ARG1$';
		$result = $this->StatusengineLegacyShell->parseCheckCommand($checkCommand);
		$expected = [
			'check_args',
			'$ARG1$'
		];
		$this->assertEquals($expected, $result);
	}

	public function testParseCheckCommandNoArgs(){
		$checkCommand = 'check_foo';
		$result = $this->StatusengineLegacyShell->parseCheckCommand($checkCommand);
		$expected = [
			'check_foo',
			null
		];
		$this->assertEquals($expected, $result);
	}

	public function testNotNull(){
		$result = $this->StatusengineLegacyShell->notNull(null);
		$expected = 0;
		$this->assertEquals($expected, $result);

		$result = $this->StatusengineLegacyShell->notNull(0);
		$expected = 0;
		$this->assertEquals($expected, $result);

		$result = $this->StatusengineLegacyShell->notNull('0');
		$expected = 0;
		$this->assertEquals($expected, $result);

		$result = $this->StatusengineLegacyShell->notNull(null, 'abc');
		$expected = 'abc';
		$this->assertEquals($expected, $result);

		$result = $this->StatusengineLegacyShell->notNull('null');
		$expected = 'null';
		$this->assertEquals($expected, $result);
	}

}
