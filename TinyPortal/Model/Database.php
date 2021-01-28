<?php
/**
 * @package TinyPortal
 * @version 1.0.0 RC1
 * @author TinyPortal - http://www.tinyportal.net
 * @license BSD 3.0 http://opensource.org/licenses/BSD-3-Clause/
 *
 * Copyright (C) 2020 - The TinyPortal Team
 *
 */
namespace TinyPortal\Model;

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
