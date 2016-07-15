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
*                   Perfdata Backend Extension for Rrdtool
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
* This extension for statusengine uses the parsed performance data and
* create or update graphs based on rrdtool.
* So you dont need to install any additional software to get this job done
*
**********************************************************************************/

class PerfdataBackendTask extends AppShell{

	public $Config = [];

	private $rrdEnabled = false;

	private $graphiteEnabled = false;

	public function init($Config){
		App::uses('File', 'Utility');
		$this->Config = $Config;

		$this->rrdEnabled = $this->isRrdEnabled();
		$this->graphiteEnabled = $this->isGraphiteEnabled();
	}

	private function isRrdEnabled(){
		return in_array('Rrd', $this->Config['perfdata_storage']);
	}

	private function isGraphiteEnabled(){
		return in_array('Graphite', $this->Config['perfdata_storage']);
	}


	public function saveToRrd(){
		return $this->rrdEnabled;
	}

	public function saveToGraphite(){
		return $this->graphiteEnabled;
	}
}
