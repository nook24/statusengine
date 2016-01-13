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
* Statusengine CLI Error handler to log Errors and Exception to Statusengine's 
* core log file defined in app/Config/Statusengine.php
*
**********************************************************************************/
class AppError{
	
	/** @var string path to the logfile */
	public static $logfile = '';

	/** @var ressource of the logile */
	public static $loghandle = null;

	public static function handleError($code, $description, $file = null,$line = null, $context = null){
		if(!is_resource(self::$loghandle)){
			self::openLog();
		}
		
		fwrite(self::$loghandle, 'Error: '.$code.PHP_EOL);
		fwrite(self::$loghandle, $description.PHP_EOL);
		fwrite(self::$loghandle, 'File: '.$file);
		fwrite(self::$loghandle, ' Line: '.$line.PHP_EOL);
		
		if(is_array($context) || is_object($context)){
			fwrite(self::$loghandle, var_export($context, true));
			return;
		}

		fwrite(self::$loghandle, $context.PHP_EOL);
	}

	public static function handleException($error){
		if(!is_resource(self::$loghandle)){
			self::openLog();
		}
		fwrite(self::$loghandle, $error);
	}

	public static function openLog(){
		if(self::$logfile == ''){
			Configure::load('Statusengine');
			self::$logfile = Configure::read('logfile');
		}
		self::$loghandle = fopen(self::$logfile, 'a+');
	}
}

