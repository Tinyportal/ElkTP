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

class Category extends Base {

    private $dBStructure        = array();
    private static $_instance   = null;

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
            'id'                => 'int',
            'item_type'         => 'text',
            'item_id'           => 'int',
            'access'            => 'text',
            'display_name'      => 'text',
            'short_name'        => 'text',
            'settings'          => 'text',
            'custom_template'   => 'int',
        );

    }}}

    public function getCategory( $category_id ) {{{

        if(empty($category_id)) {
            return;
        }

        $shout = array();

        $request =  $this->dB->db_query('', '
            SELECT * FROM {db_prefix}tp_categories
            WHERE id = {int:id} LIMIT 1',
            array (
                'id' => $category_id
            )
        );

        if($this->dB->db_num_rows($request) > 0) {
            $shout = $this->dB->db_fetch_assoc($request);
        }

        return $shout;

    }}}

    public function getCategoryData( $columns, $where ) {{{

        return self::getSQLData($columns, $where, $this->dBStructure, 'tp_categories');

    }}}

   public function insertCategory($category_data) {{{

        return self::insertSQL($category_data, $this->dBStructure, 'tp_categories');

    }}}

     public function updateCategory($category_id, $category_data) {{{

        return self::updateSQL($category_id, $category_data, $this->dBStructure, 'tp_categories');

    }}}

    public function deleteCategory( $category_id ) {{{

        return self::deleteSQL($category_id, 'tp_categories');

    }}}

}

?>
