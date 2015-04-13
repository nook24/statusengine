<?php
class LogfileTask extends AppShell{
	
	public function init(){
		Configure::load('Statusengine');
		$this->logfile = Configure::read('logfile');
		$this->log = null;
		$this->open();
	}
	
	public function stlog($str = ''){
		if(!is_resource($this->log)){
			$this->open();
		}
		fwrite($this->log, $str.PHP_EOL);
	}
	
	public function clog($str = ''){
		$this->stlog('['.getmypid().'] '.$str);
	}
	
	public function open(){
		$this->log = fopen($this->logfile, 'a+');
	}
	
	public function welcome(){
		$this->stlog('');
		$this->stlog('    #####');
		$this->stlog('   #     # #####   ##   ##### #    #  ####  ###### #    #  ####  # #    # ######');
		$this->stlog('   #         #    #  #    #   #    # #      #      ##   # #    # # ##   # #');
		$this->stlog('    #####    #   #    #   #   #    #  ####  #####  # #  # #      # # #  # #####');
		$this->stlog('         #   #   ######   #   #    #      # #      #  # # #  ### # #  # # #');
		$this->stlog('   #     #   #   #    #   #   #    # #    # #      #   ## #    # # #   ## #');
		$this->stlog('    #####    #   #    #   #    ####   ####  ###### #    #  ####  # #    # ######');
		$this->stlog('');
		$this->stlog('                            the missing event broker');
		$this->stlog('');
		$this->stlog('Copyright (c) 2014 - present Daniel Ziegler <daniel@statusengine.org>');
		$this->stlog('Please visit http://www.statusengine.org for more information');
		$this->stlog('Contribute to Statusenigne at: https://github.com/nook24/statusengine');
		$this->stlog('');
		$this->stlog('');
		$this->stlog('');
	}
}