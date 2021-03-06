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

class Mentions extends Base {

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
    }}}

    public function addJS() {{{
        // Mentions
        if (!empty($this->modSettings['enable_mentions']) && allowedTo('mention')) {
            loadJavaScriptFile('jquery.atwho.min.js',               array('defer' => true, 'minimize' => false), 'tp_atwho');
            loadJavaScriptFile('jquery.caret.min.js',               array('defer' => true, 'minimize' => false), 'tp_caret');
            loadJavaScriptFile('tinyportal/tinyPortalMentions.js',  array('defer' => true, 'minimize' => false), 'tp_mentions');
        }
    }}}

    public function getMention( $mention_id ) {{{

    }}}

    public function addMention( $mention ) {{{
        if (!empty($this->modSettings['enable_mentions'])) {
            require_once(SUBSDIR . '/Post.subs.php');
            require_once(SUBSDIR . '/Mentions.subs.php');
            $mentions = \Mentions::getMentionedMembers($mention['content']);
            if (is_array($mentions)) {
                \Mentions::insertMentions($mention['type'], $mention['id'], $mentions, $mention['member_id']);
                $mention['content'] = \Mentions::getBody($mention['content'], $mentions);
                foreach($mentions as $id => $member) {
                    $insert_rows[] = array(
                        'alert_time'        => time(),
                        'id_member'         => $member['id'],
                        'id_member_started' => $mention['member_id'],
                        'member_name'       => $mention['username'],
                        'content_type'      => $mention['type'],
                        'content_id'        => $mention['id'],
                        'content_action'    => $mention['action'],
                        'is_read'           => 0,
                        'extra' => Util::json_encode(
                            array (
                                "text"          => $mention['text'],
                                "user_mention"  => $mention['username'],
                                "event_title"   => $mention['event_title'],
                            )
                        ),
                    );

                    $this->dB->db_insert('insert',
                        '{db_prefix}user_alerts',
                        array(
                            'alert_time'        => 'int',
                            'id_member'         => 'int',
                            'id_member_started' => 'int',
                            'member_name'       => 'string',
                            'content_type'      => 'string',
                            'content_id'        => 'int',
                            'content_action'    => 'string',
                            'is_read'           => 'int',
                            'extra'             => 'string'
                            ),
                        $insert_rows,
                        array('id_alert')
                    );
                    updateMemberData($member['id'], ['alerts' => '+']);
                }
            }
        }
    }}}

    public function removeMention( $mention_id ) {{{

    }}}
}

?>
