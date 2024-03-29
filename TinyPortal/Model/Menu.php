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

    public function select( $columns, $where ) {{{

        return self::getSQLData($columns, $where, $this->dBStructure, 'tp_menu');

    }}}

    public function insert($menu_data) {{{

        return self::insertSQL($menu_data, $this->dBStructure, 'tp_menu');

    }}}

    public function update($menu_id, $menu_data) {{{

        return self::updateSQL($menu_id, $menu_data, $this->dBStructure, 'tp_menu');

    }}}

    public function delete( $menu_id ) {{{

        return self::deleteSQL($menu_id, 'tp_menu');

    }}}

    public function list($start, $items_per_page, $sort) {{{
        $menus  = array();

        $menu   = self::select(array('id', 'name' ,'type', 'link', 'parent', 'permissions', 'enabled'), '');
        if(is_array($menu)) {
            foreach($menu as $id => $row) {
                $menus[$id] = array ( 
                        'id'            => $row['id'],
                        'title'         => $row['name'],
                        'category'      => $row['type'],
                        'member'        => $row['parent'],
                        'dt_published'  => $row['link'],
                        'status'        => $row['enabled'],
                );
            }
        }

        return $menus;
    }}}

    public function total() {{{

        $menu = self::select(array('id', 'name' ,'type', 'link', 'parent', 'permissions', 'enabled'), '');

        return is_countable($menu) ? count($menu) : 0;
    }}}
}

?>
