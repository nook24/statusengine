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
* @license http://www.opensource.org/licenses/mit-license.php MIT License
* May thanks to the CakePHP community :)
*
* --------------------------------------------------------------------------------
*
* Statusengine CLI Error handler to log Errors and Exception to SysLog
*
**********************************************************************************/

class AppError extends ErrorHandler{
	/**
	 * Stripped version of CakePHP's ErrorHandler::handleError()
	 *
	 * @param int $code Code of error
	 * @param string $description Error description
	 * @param string $file File on which error occurred
	 * @param int $line Line that triggered the error
	 * @param array $context Context
	 * @return bool true if error was handled
	 */
	public static function handleError($code, $description, $file = null,$line = null, $context = null){
		list($error, $log) = self::mapErrorCode($code);
		$message = $error . ' (' . $code . '): ' . $description . ' in [' . $file . ', line ' . $line . ']';
		$trace = Debugger::trace(array('start' => 1, 'format' => 'log'));
		$message .= "\nTrace:\n" . $trace . "\n";
		return CakeLog::write($log, $message);
	}

	/**
	 * Stripped version of CakePHP's ErrorHandler::handleException()
	 *
	 * @param Exception $exception The exception to render.
	 * @return bool true if error was handled
	 */
	public static function handleException($exception){
		return CakeLog::write(LOG_ERR, self::_getMessage($exception));
	}
}
