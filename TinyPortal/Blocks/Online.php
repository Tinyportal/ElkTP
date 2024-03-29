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
namespace TinyPortal\Blocks;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Online extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $block['title']     = '<a class="subject"  href="'.$this->scripturl.'?action=who">'.$block['title'].'</a>';

    }}}

    public function display( $block ) {{{

        if($block['avatar'] == 0) {
            require_once(SUBSDIR . '/MembersOnline.subs.php');
            $membersOnlineOptions = array(
                'show_hidden'   => \allowedTo('moderate_forum'),
                'sort'          => 'log_time',
                'reverse_sort'  => true,
            );
            $whos = \getMembersOnlineStats($membersOnlineOptions);

            echo '
                <div>
                    ' . $whos['num_guests'] .' ' , $whos['num_guests'] == 1 ? $this->txt['guest'] : $this->txt['guests'] , ',
                    ' . $whos['num_users_online'] .' ' , $whos['num_users_online'] == 1 ? $this->txt['user'] : $this->txt['users'] , '
                </div>';

            if(isset($whos['users_online']) && count($whos['users_online']) > 0) {
                $ids    = array();
                $names  = array();
                $times  = array();
                foreach($whos['users_online'] as $w => $wh) {
                    // For reasons historical, ELK produces the timestamp as
                    // the timestamp followed by the user's name, so let's fix it.
                    $timestamp          = (int) strtr($w, array($wh['username'] => ''));
                    $ids[]              = $wh['id'];
                    $names[$wh['id']]   = $wh['name'];
                    $times[$wh['id']]   = \standardTime($timestamp);
                }

                $avy = \TinyPortal\Model\Subs::getInstance()->getAvatars($ids);
                foreach($avy as $a => $av) {
                    echo '<a class="tp_avatar_single2" title="'.$names[$a].'" href="' . $this->scripturl . '?action=profile;u='.$a.'">' . $av . '</a>';
                }
            }
        }
        else {
            echo '<div style="line-height: 1.4em;">' , \ssi_whosOnline() , '</div>';
        }

    }}}

    public function admin_setup( &$block ) {{{

		parent::admin_setup($block);

		$default = array(
			'avatar'	=> 0,
		);

		if(empty($block['settings'])) {
			$block += $default;
		}

    }}}

    public function admin_display( $block ) {{{

		echo '
			<hr>
			<dl class="tptitle settings">
				<dt>
					<label for="field_name">'.$this->txt['tp-rssblock-showavatar'].'</label>
				</dt>
				<dd>
					<input type="radio" name="tp_block_set_avatar" value="1" ' , ($block['avatar'] == '1' || $block['avatar'] == '') ? ' checked' : '' ,'>'.$this->txt['tp-yes'].' <input type="radio" name="tp_block_set_avatar" value="0" ' , ($block['avatar'] == '0') ? ' checked' : '' ,'>'.$this->txt['tp-no'].'
				</dd>
			</dl>';

    }}}

}

?>
