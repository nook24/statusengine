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
*                               Perfdata Extension
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
* --------------------------------------------------------------------------------
*
* This extension for statusengine is able to parse performance
*
**********************************************************************************/

class PerfdataTask extends AppShell{

	/**
	 * Parse perfdata of the naemon plugin output to an array
	 *
	 * @param string $perfdataString and string with monitoring performacne data
	 * @return array An array with the gauges and perfdata values
	 */
	function parsePerfdataString($perfdataString){
		$return = [];

		$gauges = $this->splitGauges($perfdataString);
		foreach($gauges as $gauge){
			$result = $this->parseGauge($gauge);
			$return = array_merge($return, $result);
		}
		return $return;
	}

	/**
	 * Split the given perfdata string into gauges
	 * @param string $perfdataString => rta=0.069000ms;100.000000;500.000000;0.000000 pl=0%;20;60;0
	 * @return array An array with the found gauges
	 */
	public function splitGauges($perfdataString){
		$perfdataString = trim($perfdataString);
		$len = strlen($perfdataString);
		$pointer = 0;

		$gauges = [];
		$gauge = '';

		$state = 'SEARCH_SPACE';
		while($len > $pointer){
			$char = $perfdataString[$pointer];
			if(($char == "'" || $char == '"') && $state == 'SEARCH_SPACE'){
				//we found a starting quote
				$state = 'SEARCH_QUOTE';
				if($char == '"'){
					$char = "'";
				}
				$gauge .= $char;

				$pointer++;
				continue;
			}

			switch($state){
				case 'SEARCH_QUOTE':
					if(($char == "'" || $char == '"') && $state == 'SEARCH_QUOTE'){
						//we found the ending quote
						if($char == '"'){
							$char = "'";
						}
						$gauge .= $char;
						$state = 'SEARCH_SPACE';
						$pointer++;
						continue 2;
					}
				break;

				case 'SEARCH_SPACE':
					if($char == ' '){
						$gauges[] = $gauge;
						$gauge = '';
						$pointer++;
						continue 2;
					}
				break;
			}
			$gauge .= $char;
			$pointer++;

			//reach end of strig?
			if($pointer == $len){
				$gauges[] = $gauge;
			}
		}
		return $gauges;
	}

	/**
	* Split the given gauge into the perfdata values
	* like current. unit, warning, critical, min and max
	* @param string $perfdataString => rta=0.069000ms;100.000000;500.000000;0.000000
	* @return array An array with the gauge
	*/
	public function parseGauge($gauge){
		$len = strlen($gauge);
		$pointer = 0;
		$gaugeName = '';
		$result = [
			'current' => null,
			'unit' => null,
			'warning' => null,
			'critical' => null,
			'min' => null,
			'max' => null
		];
		$state = 'SEARCH_GAUGE_NAME';
		while($len > $pointer){
			$char = $gauge[$pointer];
			switch($state){
				case 'SEARCH_GAUGE_NAME':
					if($char == '='){
						$state = 'SEARCH_VALUE';
						$pointer++;
						continue 2;
					}
					$gaugeName .= $char;
				break;

				case 'SEARCH_VALUE':
					if($char == '.' || $char == ',' || ($char >= '0' && $char <= '9')){
						$char = $this->makeUsDecimal($char);
						$result['current'] .= $char;
					}else{
						//not numeric, i guess we found the unit
						$state = 'SEARCH_UNIT';
						continue 2;
					}
				break;

				case 'SEARCH_UNIT':
					if($char == ';'){
						$state = 'SEARCH_WARNING';
						$pointer++;
						continue 2;
					}
					$result['unit'] .= $char;
				break;

				case 'SEARCH_WARNING':
					if($char == ';'){
						$state = 'SEARCH_CRITICAL';
						$pointer++;
						continue 2;
					}
					$char = $this->makeUsDecimal($char);
					$result['warning'] .= $char;
				break;

				case 'SEARCH_CRITICAL':
					if($char == ';'){
						$state = 'SEARCH_MINIMUM';
						$pointer++;
						continue 2;
					}
					$char = $this->makeUsDecimal($char);
					$result['critical'] .= $char;
				break;

				case 'SEARCH_MINIMUM':
					if($char == ';'){
						$state = 'SEARCH_MAXIMUM';
						$pointer++;
						continue 2;
					}
					$char = $this->makeUsDecimal($char);
					$result['min'] .= $char;
				break;

				case 'SEARCH_MAXIMUM':
					if($char == ';'){
						$pointer++;
						$state = 'DONE';
						continue 2;
					}
					$char = $this->makeUsDecimal($char);
					$result['max'] .= $char;
				break;
			}
			$pointer++;
		}

		//Fix %% unit issue
		if($result['unit'] == '%%'){
			$result['unit'] = '%';
		}

		$return = [
			$gaugeName => $result
		];
		return $return;
	}

	/**
	 * Form a number in US decimal
	 * @param int a EU or US decimal number
	 * @return int a numer in US decimal format
	 */
	public function makeUsDecimal($number){
		return str_replace(',', '.', $number);
	}
}
