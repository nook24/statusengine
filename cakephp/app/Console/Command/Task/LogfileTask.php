<?php
class LogfileTask extends AppShell{
	
	public function init(){
		Configure::load('Statusengine');
		$this->logfile = Configure::read('logfile');
		$this->log = null;
		$this->open();
	}
	
	public function log($str = ''){
		if(!is_resource($this->log)){
			$this->open();
		}
		fwrite($this->log, $str.PHP_EOL);
	}
	
	public function clog($str = ''){
		$this->log('['.getmypid().'] '.$str);
	}
	
	public function open(){
		$this->log = fopen($this->logfile, 'a+');
	}
	
	public function welcome(){
		$this->log('');
		$this->log('    #####');
		$this->log('   #     # #####   ##   ##### #    #  ####  ###### #    #  ####  # #    # ######');
		$this->log('   #         #    #  #    #   #    # #      #      ##   # #    # # ##   # #');
		$this->log('    #####    #   #    #   #   #    #  ####  #####  # #  # #      # # #  # #####');
		$this->log('         #   #   ######   #   #    #      # #      #  # # #  ### # #  # # #');
		$this->log('   #     #   #   #    #   #   #    # #    # #      #   ## #    # # #   ## #');
		$this->log('    #####    #   #    #   #    ####   ####  ###### #    #  ####  # #    # ######');
		$this->log('');
		$this->log('                            the missing event broker');
		$this->log('');
		$this->log('Copyright (c) 2014 - present Daniel Ziegler <daniel@statusengine.org>');
		$this->log('Please visit http://www.statusengine.org for more information');
		$this->log('Contribute to Statusenigne at: https://github.com/nook24/statusengine');
		$this->log('');
		$this->log('');
		$this->log('');
	}
}