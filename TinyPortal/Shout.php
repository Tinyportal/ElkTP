<?php
/**
 * @package TinyPortal
 * @version 1.0.0
 * @author TinyPortal - http://www.tinyportal.net
 * @license BSD 3.0 http://opensource.org/licenses/BSD-3-Clause/
 *
 * Copyright (C) 2020 - The TinyPortal Team
 *
 */
namespace TinyPortal;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Shout extends Base {

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
            'id'            => 'int',
            'shoutbox_id'   => 'int',
            'content'       => 'text', 
            'time'          => 'text',
            'member_link'   => 'text',
            'member_ip'     => 'text',
            'member_id'     => 'int',
            'type'          => 'text',  // tinytext
            'sticky'        => 'int',   // smallint
            'sticky_layout' => 'text',
            'edit'          => 'text',
        );

    }}}

    public function getShouts( $shoutbox_id = null ) {{{

        $shout = array();

        if(!is_null($shoutbox_id)) {
            $where          = '{int:shoutbox_id}';
            $where_array    = array( 'shoutbox_id' => $shoutbox_id );
        }
        else {
            $where          = '1=1';
            $where_array    = array( );
        }

        $request =  $this->dB->db_query('', '
            SELECT * FROM {db_prefix}tp_shoutbox
            WHERE '.$where,
            $where_array
        );

        if($this->dB->db_num_rows($request) > 0) {
            while ( $shout = $this->dB->db_fetch_assoc($request) ) {
                $shout[] = $shout;
            }
        }

        $this->dB->db_free_result($request);

        return $shout;

    }}}

    public function getShout( $shout_id ) {{{

        if(empty($shout_id)) {
            return;
        }

        $shout = array();

        $request =  $this->dB->db_query('', '
            SELECT * FROM {db_prefix}tp_shoutbox
            WHERE id = {int:shoutid} LIMIT 1',
            array (
                'shoutid' => $shout_id
            )
        );

        if($this->dB->db_num_rows($request) > 0) {
            $shout = $this->dB->db_fetch_assoc($request);
        }

        return $shout;

    }}}

    public function getShoutData( $columns, $where ) {{{

        return self::getSQLData($columns, $where, $this->dBStructure, 'tp_shoutbox');

    }}}

   public function insertShout($shout_data) {{{

        return self::insertSQL($shout_data, $this->dBStructure, 'tp_shoutbox');

    }}}

     public function updateShout($shout_id, $shout_data) {{{

        return self::updateSQL($shout_id, $shout_data, $this->dBStructure, 'tp_shoutbox');

    }}}

    public function deleteShout( $shout_id ) {{{

        return self::deleteSQL($shout_id, 'tp_shoutbox');

    }}}

}

?>
