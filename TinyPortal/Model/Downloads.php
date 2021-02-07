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

class Download extends Base
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
            'category_id'   => 'int',
            'member_id'     => 'int',
            'member_name'   => 'string',
            'dt_created'    => 'int',
            'dt_published'  => 'int',
            'title'         => 'string',
            'body'          => 'string',
            'image_name'    => 'string',
            'type'          => 'string',
            'date'          => 'int',
            'permissions'   => 'string',
            'styles'        => 'string',
            'views'         => 'int',
            'comments'      => 'int',
            'status'        => 'int',
        );

    }}}

    public function getDownloadData( $columns, $where ) {{{

        return self::getSQLData($columns, $where, $this->dBStructure, 'tp_download');

    }}}

   public function insertDownload($download_data) {{{

        return self::insertSQL($download_data, $this->dBStructure, 'tp_download');

    }}}

     public function updateDownload($download_id, $download_data) {{{

        return self::updateSQL($download_id, $download_data, $this->dBStructure, 'tp_download');

    }}}

    public function deleteDownload( $download_id ) {{{

        return self::deleteSQL($download_id, 'tp_download');

    }}}

}

?>
