<?php
/**
 * @package TinyPortal
 * @version 1.0.0 RC3
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


	/*
	*	Provide a static method to all database calls
	*/
	public static function __callStatic($call, $vars) {{{

		$ret = false;

        if(is_callable(array(self::getInstance(), $call), false)) {
			$ret = call_user_func_array(array(self::getInstance(), $call), $vars);
		}

		return $ret;

	}}}

	/*
	*	Provide a static object instance (Singleton)
	*/
    public static function getInstance() {{{

    	if(self::$_instance == null) {
			self::$_instance = new self();
		}

    	return self::$_instance;

    }}}

    // Empty Clone method
    private function __clone() { }

	/*
	*	Call underlying database functions and disable query check if we are using a '
	*/
	public function __call($call, $vars) {{{
		global $modSettings;

		$ret	= false;
        $dB		= \database();

        // Compatability with smf db_ methods
        $call = str_replace('db_', '', $call);
		if($call == 'query' && isset($vars[1]) && strpos($vars[1], '\'') !== false) {
			$oldModSetting = isset($modSettings['disableQueryCheck']) ? $modSettings['disableQueryCheck'] : false;
			$modSettings['disableQueryCheck'] = true;
		}

        if(is_callable(array($dB, $call), false)) {
            $ret = call_user_func_array(array($dB, $call), $vars);
        }

		if($call == 'query' && isset($vars[1]) && strpos($vars[1], '\'') !== false) {
			$modSettings['disableQueryCheck'] = $oldModSetting;
		}

		return $ret;
	}}}

}

?>
