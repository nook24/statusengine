<?php
class PerfdataTaskTest extends CakeTestCase {

	// run test in bash
	// while true; do clear;./cake test app Console/Command/Task/PerfdataTask; sleep 5; done

	public function setUp(){
		$taskCollection = new TaskCollection(new Shell());
		$this->Perfdata =  $taskCollection->load('Perfdata');
	}

	public function testSplitGaugesRta(){
		$perfdata = 'rta=0.069000ms;100.000000;500.000000;0.000000 pl=0%;20;60;0';
		$result = $this->Perfdata->splitGauges($perfdata);
		$expected = [
			'rta=0.069000ms;100.000000;500.000000;0.000000',
			'pl=0%;20;60;0'
		];
		$this->assertEquals($expected, $result);
	}

	public function testSplitGaugesMinimal(){
		$perfdata = 'foo=1';
		$result = $this->Perfdata->splitGauges($perfdata);
		$expected = [
			'foo=1',
		];
		$this->assertEquals($expected, $result);
	}

	public function testSplitGaugesSingleQuotes(){
		$perfdata = "'foo bar'=1";
		$result = $this->Perfdata->splitGauges($perfdata);
		$expected = [
			"'foo bar'=1",
		];
		$this->assertEquals($expected, $result);
	}

	public function testSplitGaugesDoubleQuotes(){
		$perfdata = '"foo bar"=1';
		$result = $this->Perfdata->splitGauges($perfdata);
		$expected = [
			"'foo bar'=1",
		];
		$this->assertEquals($expected, $result);
	}

	public function testParseGaugeRta(){
		$gauge = 'rta=0.069000ms;100.000000;500.000000;0.000000;150,150';
		$result = $this->Perfdata->parseGauge($gauge);
		$expected = [
			'rta' => [
				'current' => '0.069000',
				'unit' => 'ms',
				'warning' => '100.000000',
				'critical' => '500.000000',
				'min' => '0.000000',
				'max' => '150.150'
			]
		];
		$this->assertEquals($expected, $result);
	}

	//Perfdata->parseGauge gets only called after Perfdata->splitGauges which
	//is removing all double quotes
	public function testParseGaugeSingleQuotes(){
		$gauge = "'foo bar'=1ms";
		$result = $this->Perfdata->parseGauge($gauge);
		$expected = [
			"'foo bar'" => [
				'current' => '1',
				'unit' => 'ms',
				'warning' => null,
				'critical' => null,
				'min' => null,
				'max' => null
			]
		];
		$this->assertEquals($expected, $result);
	}

	public function testParseRta(){
		$perfdata = 'rta=0.069000ms;100.000000;500.000000;0.000000 pl=0%;20;60;0';
		$result = $this->Perfdata->parsePerfdataString($perfdata);

		$expected = [
			'rta' => [
				'current' => '0.069000',
				'unit' => 'ms',
				'warning' => '100.000000',
				'critical' => '500.000000',
				'min' => '0.000000',
				'max' => null
			],
			'pl' => [
				'current' => '0',
				'unit' => '%',
				'warning' => '20',
				'critical' => '60',
				'min' => '0',
				'max' => null
			]
		];
		$this->assertEquals($expected, $result);
	}

	public function testParseMinimal(){
		$perfdata = 'foo=1';
		$result = $this->Perfdata->parsePerfdataString($perfdata);

		$expected = [
			'foo' => [
				'current' => '1',
				'unit' => '',
				'warning' => null,
				'critical' => null,
				'min' => null,
				'max' => null
			],
		];
		$this->assertEquals($expected, $result);
	}

	public function testParseUnit(){
		$perfdata = 'foo=1ms';
		$result = $this->Perfdata->parsePerfdataString($perfdata);

		$expected = [
			'foo' => [
				'current' => '1',
				'unit' => 'ms',
				'warning' => null,
				'critical' => null,
				'min' => null,
				'max' => null
			],
		];
		$this->assertEquals($expected, $result);
	}

	public function testParseSingleQuotes(){
		$perfdata = "'foo bar'=1ms";
		$result = $this->Perfdata->parsePerfdataString($perfdata);

		$expected = [
			"'foo bar'" => [
				'current' => '1',
				'unit' => 'ms',
				'warning' => null,
				'critical' => null,
				'min' => null,
				'max' => null
			],
		];
		$this->assertEquals($expected, $result);
	}

	public function testParseDoubleQuotes(){
		$perfdata = '"foo bar"=1ms';
		$result = $this->Perfdata->parsePerfdataString($perfdata);

		$expected = [
			"'foo bar'" => [
				'current' => '1',
				'unit' => 'ms',
				'warning' => null,
				'critical' => null,
				'min' => null,
				'max' => null
			],
		];
		$this->assertEquals($expected, $result);
	}

}
