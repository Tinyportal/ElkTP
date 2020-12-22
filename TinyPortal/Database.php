<?php
/**
 * Handles all TinyPortal Database operations
 *
 * @name      	TinyPortal
 * @package 	Database
 * @copyright 	TinyPortal
 * @license   	MPL 1.1
 *
 * This file contains code covered by:
 * author: tinoest - https://tinoest.co.uk
 * license: BSD-3-Clause 
 *
 * @version 1.0.0
 *
 */
namespace TinyPortal;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Database
{
    private static $_instance   = null;

    public static function getInstance() {{{
	
    	if(self::$_instance == null) {
			self::$_instance = new self();
		}
	
    	return self::$_instance;
	
    }}}

    // Empty Clone method
    private function __clone() { }

	public function __call($call, $vars) {{{

        //debug_print_backtrace();

        $dB = \database();

        // Compatability with smf db_ methods
        $call = str_replace('db_', '', $call);
        if(is_callable(array($dB, $call), false)) { 
            return call_user_func_array(array($dB, $call), $vars);
        }
        else {
		    return false;
        }

	}}}

}

?>
