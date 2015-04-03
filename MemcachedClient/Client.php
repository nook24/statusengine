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
*                           Memcached Extension Client
*
* --------------------------------------------------------------------------------
*
* The MIT License (MIT)
* 
* Copyright (c) <2015> <Daniel Ziegler>
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
* --------------------------------------------------------------------------------
*
* With Statusengine Memcached Client you can acces status data stored in the
* memory based database provided by memcached
*
*
**********************************************************************************/

namespace StatusengineMemory;

class StatusengineMemoryClient{
	protected $__server = '127.0.0.1';
	protected $__port = 11211;
	protected $Memcached = null;
	
	public function __construct($server, $port){
		require_once 'Models/Model.php';
		require_once 'Models/Hoststatus.php';
		require_once 'Models/Servicestatus.php';
		
		$this->StatusengineMemoryClient($server, $port);
	}
	
	public function StatusengineMemoryClient($server, $port){
		$this->__server      = $server;
		$this->__port        = $port;
		$this->Memcached     = new \Memcached;
		$this->Hoststatus    = new Hoststatus($this->Memcached);
		$this->Servicestatus = new Servicestatus($this->Memcached);
		return $this->Memcached->addServer($server, $port);
	}
	
}
