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

class Stats extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $block['title'] = '<a class="subject"  href="'.$this->scripturl.'?action=stats">'.$block['title'].'</a>';

    }}}

    public function display( $block ) {{{

        $bullet = '<img src="'.$this->settings['tp_images_url'].'/TPdivider.png" alt="" class="tpbullet">';
        $bullet2 = '<img src="'.$this->settings['tp_images_url'].'/TPdivider2.png" alt="" class="tpbullet">';

        echo'
            <div class="tp_statsblock">';

        if(isset($this->context['TPortal']['userbox']['stats'])) {
            // members stats
            echo '
                <h5 class="mlist"><a href="'.$this->scripturl.'?action=memberlist">'.$this->txt['members'].'</a></h5>
                <ul>
                <li>' . $bullet. $this->txt['total_members'].': ' , isset($this->modSettings['memberCount']) ? $this->modSettings['memberCount'] : $this->modSettings['totalMembers'] , '</li>
                <li>' . $bullet. $this->txt['tp-latest']. ': <a class="bbc_strong" href="', $this->scripturl, '?action=profile;u=', $this->modSettings['latestMember'], '">', $this->modSettings['latestRealName'], '</a></li>
                </ul>';
        }

        if(isset($this->context['TPortal']['userbox']['stats_all'])) {
            // more stats
            echo '
                <h5 class="stats"><a href="'.$this->scripturl.'?action=stats">'.$this->txt['tp-stats'].'</a></h5>
                <ul>
                <li>'.  $bullet. $this->txt['total_posts'].': '.$this->modSettings['totalMessages']. '</li>
                <li>'.  $bullet. $this->txt['total_topics'].': '.$this->modSettings['totalTopics']. '</li>
                <li>' . $bullet. $this->txt['tp-mostonline-today'].': '.$this->modSettings['mostOnlineToday'].'</li>
                <li>' . $bullet. $this->txt['tp-mostonline'].': '.$this->modSettings['mostOnline'].'</li>
                <li>('.standardTime($this->modSettings['mostDate']).')</li>
                </ul>';
        }

        if(isset($this->context['TPortal']['userbox']['online'])) {
            // add online users
            echo '
                <h5 class="online"><a href="'.$this->scripturl.'?action=who">'.$this->txt['online_users'].'</a></h5>
                <div class="tp_stats_users" style="line-height: 1.3em;">';

            $online = ssi_whosOnline('array');
            echo  $bullet. $this->txt['tp-users'].': '.$online['num_users']. '<br>
                '. $bullet. $this->txt['tp-guests'].': '.$online['guests'].'<br>
                '. $bullet. $this->txt['tp-total'].': '.$online['total_users'].'<br>
                <div style="max-height: 23em; overflow: auto;">';

            foreach($online['users'] as $user) {
                echo  $bullet2 , $user['hidden'] ? '<i>' . $user['link'] . '</i>' : $user['link'];
                echo '<br>';
            }
            echo '
                </div></div>';
        }
        echo '
            </div>';

    }}}

    public function admin_setup( &$block ) {{{

		parent::admin_setup($block);

    }}}

    public function admin_display( $block ) {{{

		echo '
			</div><div>
			<hr><dl class="tptitle settings">
				<dt>
					<label for="field_name">'.$this->txt['tp-showuserbox'].'</label>
				</dt>';

		if(isset($this->context['TPortal']['userbox']['avatar']) && $this->context['TPortal']['userbox']['avatar'])
			echo '<input type="hidden" name="tp_userbox_options0" value="avatar">';
		if(isset($this->context['TPortal']['userbox']['logged']) && $this->context['TPortal']['userbox']['logged'])
			echo '<input type="hidden" name="tp_userbox_options1" value="logged">';
		if(isset($this->context['TPortal']['userbox']['time']) && $this->context['TPortal']['userbox']['time'])
			echo '<input type="hidden" name="tp_userbox_options2" value="time">';
		if(isset($this->context['TPortal']['userbox']['unread']) && $this->context['TPortal']['userbox']['unread'])
			echo '<input type="hidden" name="tp_userbox_options3" value="unread">';
		echo '	<dd>
					<input type="checkbox" id="tp_userbox_options4" name="tp_userbox_options4" value="stats" ', (isset($this->context['TPortal']['userbox']['stats']) && $this->context['TPortal']['userbox']['stats']) ? 'checked' : '' , '><label for="tp_userbox_options4"> '.$this->txt['tp-userbox5'].'</label><br>
					<input type="checkbox" id="tp_userbox_options5" name="tp_userbox_options5" value="online" ', (isset($this->context['TPortal']['userbox']['online']) && $this->context['TPortal']['userbox']['online']) ? 'checked' : '' , '><label for="tp_userbox_options5"> '.$this->txt['tp-userbox6'].'</label><br>
					<input type="checkbox" id="tp_userbox_options6" name="tp_userbox_options6" value="stats_all" ', (isset($this->context['TPortal']['userbox']['stats_all']) && $this->context['TPortal']['userbox']['stats_all']) ? 'checked' : '' , '><label for="tp_userbox_options6"> '.$this->txt['tp-userbox7'].'</label>
				</dd>
			</dl>';


		return true;

    }}}

}

?>
