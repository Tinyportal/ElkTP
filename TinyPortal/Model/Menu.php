<?php
/**
 * @package TinyPortal
 * @version 1.0.0 RC2
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

class Menu extends Base
{

    private static $_instance   = null;
    private $dBStructure        = array();

    public static function getInstance() {{{

    	if(self::$_instance == null) {
			self::$_instance = new self();
		}

    	return self::$_instance;

    }}}

    // Empty Clone method
    private function __clone() { }

    public function __construct() {{{
        parent::__construct();

        $this->dBStructure = array (
            'id'            => 'int',
            'name'          => 'string',
            'type'          => 'string',
            'link'          => 'string',
            'parent'        => 'string',
            'permissions'   => 'string',
            'enabled'       => 'int',
        );

    }}}

    public function getMenuData( $columns, $where ) {{{

        return self::getSQLData($columns, $where, $this->dBStructure, 'tp_menu');

    }}}

   public function insertMenu($menu_data) {{{

        return self::insertSQL($menu_data, $this->dBStructure, 'tp_menu');

    }}}

     public function updateMenu($menu_id, $menu_data) {{{

        return self::updateSQL($menu_id, $menu_data, $this->dBStructure, 'tp_menu');

    }}}

    public function deleteMenu( $menu_id ) {{{

        return self::deleteSQL($menu_id, 'tp_menu');

    }}}

}

?>
