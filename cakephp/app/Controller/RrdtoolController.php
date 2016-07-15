<?php
/**
* Copyright (C) 2015 Daniel Ziegler <daniel@statusengine.org>
*
* This file is part of Statusengine.
*
* Statusengine is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 2 of the License, or
* (at your option) any later version.
*
* Statusengine is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Statusengine.  If not, see <http://www.gnu.org/licenses/>.
*/
class RrdtoolController extends AppController{
	public $uses = [
		'Rrdtool',
		'Legacy.Objects'
	];
	public $datasources = null;

	public function service($serviceObjectId = null){
		$this->layout = false;
		$this->render = false;

		$serviceObjectId = $this->request->params['named']['serviceObjectId'];
		$timespan = 3600 * 2.5;
		if(isset($this->request->params['named']['timespan']) && is_numeric($this->request->params['named']['timespan'])){
			$timespan = $this->request->params['named']['timespan'];
		}

		$ds = $this->request->params['named']['ds'];

		$object = $this->Objects->findByObjectId($serviceObjectId);
		$hostName = $object['Objects']['name1'];
		$serviceName = $object['Objects']['name2'];

		if(!isset($datasources) || !isset($datasources[$ds])){
			$datasources = $this->Rrdtool->parseXml($hostName, $serviceName);
		}

		//debug($datasources);debug($ds);

		$name = $datasources[$ds]['name'];
		$label = $datasources[$ds]['label'];
		$unit = $datasources[$ds]['unit'];

		$rrdCommand = [
			'--slope-mode',
			'--start', time() - $timespan,
			'--end', time(),

			'--width', 740,
			'--height', 250,
			'--full-size-mode',
			'--border', 1,
			'--title='.$name,
			'--vertical-label='.$unit,
			'--imgformat','PNG',
			'--color', 'BACK#FFFFFFFF',
			'DEF:var0='.$this->Rrdtool->getPath($hostName, $serviceName).':'.$ds.':AVERAGE',
			'AREA:var0#0287FA66:'.$label,
			'LINE1:var0#2e6da4',
			'VDEF:ds'.$ds.'avg=var0,AVERAGE',
			'GPRINT:ds'.$ds.'avg:'.__('Average').'\:%6.2lf %S',
			'VDEF:ds'.$ds.'min=var0,MINIMUM',
			'GPRINT:ds'.$ds.'min:'.__('Minimum').'\:%6.2lf %S',
			'VDEF:ds'.$ds.'max=var0,MAXIMUM',
			'GPRINT:ds'.$ds.'max:'.__('Maximum').'\:%6.2lf %S',
		];

		$defaultTimezone = date_default_timezone_get();
		if(strlen($defaultTimezone) < 2){
			$defaultTimezone = 'Europe/Berlin';
		}

		putenv('TZ='.$defaultTimezone);
		$fileName = TMP.sha1(uniqid().microtime()).'_graph.png';
		$res = rrd_graph($fileName, $rrdCommand);

		$error = rrd_error();

		if($res && $error === false){
			$this->response->file($fileName);
			// Return response object to prevent controller from trying to render a view
			return $this->response;
		}

		//We got an error from Rrdtool
		//Output error as image
		$errorImage = $this->Rrdtool->createErrorImage($error);
		$this->response->type('png');
		$this->response->body(imagepng($errorImage));
		return $this->response;
	}
}
