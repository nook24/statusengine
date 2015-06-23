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
	public $uses = 'Rrdtool';
	public $datasources = null;
	
	public function service($serviceObjectId = null){
		$this->layout = false;
		$this->render = false;
		
		$hostName = $this->request->params['named']['hostName'];
		$serviceName = $this->request->params['named']['serviceName'];
		$ds = $this->request->params['named']['ds'];
		
		if(!isset($datasources) || !isset($datasources[$ds])){
			$datasources = $this->Rrdtool->parseXml($hostName, $serviceName);
		}
		
		$name = $datasources[$ds]['name'];
		$label = $datasources[$ds]['label'];
		$unit = $datasources[$ds]['unit'];
		
		$rrdCommand = [
			'--slope-mode',
			'--start', time() - (2.5 * 3600),
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
		$fileName = TMP.sha1(rand().rand().rand()).'_graph.png';
		$res = rrd_graph($fileName, $rrdCommand);
		
		if($res){
			header('Content-Type: image/png');
		}else{
			//Debuging stuff
			debug(rrd_error());
			debug($res);
		}
		
		$image = imagecreatefrompng($fileName);
		imagepng($image);
		imagedestroy($image);
		unlink($fileName);
	}
}
