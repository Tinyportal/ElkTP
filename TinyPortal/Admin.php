<?php
/**
 * @package TinyPortal
 * @version 1.0.0
 * @author tinoest - http://www.tinyportal.net
 * @founder Bloc
 * @license MPL 2.0
 *
 * The contents of this file are subject to the Mozilla Public License Version 2.0
 * (the "License"); you may not use this package except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Copyright (C) 2018 - The TinyPortal Team
 *
 */
namespace TinyPortal;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Admin extends Base {

    private $dBStructure        = array();
    private $tpSettings         = array();
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
            'id'        => 'mediumint',
            'name'      => 'text',
            'value'     => 'text',
        );
        
        $this->tpSettings = $this->getSetting();

    }}}

    public function getSettingData( $columns, array $where ) {{{

        return self::getSQLData($columns, $where, $this->dBStructure, 'tp_settings');

    }}}

    public function getSetting( string $setting_name = null , bool $refresh = false ) {{{

        if($refresh == false && !is_null($setting_name) && array_key_exists($setting_name, $this->tpSettings)) {
            return $this->tpSettings[$setting_name];
        }

        $settings = array();

        if(empty($setting_name)) {
            $request =  $this->dB->db_query('', '
                SELECT name, value 
                FROM {db_prefix}tp_settings
                WHERE 1=1'
            );

            if($this->dB->db_num_rows($request) > 0) {
                while($row = $this->dB->db_fetch_assoc($request)) {
                    $settings[$row['name']] = $row['value'];
                }
            }
        }
        else {
            $request =  $this->dB->db_query('', '
                SELECT value FROM {db_prefix}tp_settings
                WHERE name = {string:setting_name} LIMIT 1',
                array (
                    'setting_name' => $setting_name
                )
            );

            if($this->dB->db_num_rows($request) > 0) {
                $row                        = $this->dB->db_fetch_assoc($request);
                $settings[$setting_name]    = $row['value'];
                return $row['value'];
            }
        }

        return $settings;

    }}}

   public function insertSetting( array $settings_data) {{{

        return self::insertSQL($settings_data, $this->dBStructure, 'tp_settings');

    }}}

     public function updateSetting( int $settings_id, array $settings_data) {{{

        return self::updateSQL($settings_id, $settings_data, $this->dBStructure, 'tp_settings');

    }}}

    public function deleteSetting( int $settings_id ) {{{

        return self::deleteSQL($settings_id, 'tp_settings');

    }}}

}

?>
