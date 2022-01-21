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

        $bullet = '<img src="'.$this->settings['tp_images_url'].'/TPdivider.png" alt=""  style="margin:0 4px 0 0;" />';
        $bullet2 = '<img src="'.$this->settings['tp_images_url'].'/TPdivider2.png" alt="" style="margin:0 4px 0 0;" />';

        echo'
            <div class="tp_statsblock">';

        if(isset($this->context['TPortal']['userbox']['stats'])) {
            // members stats
            echo '
                <h5 class="mlist"><a href="'.$this->scripturl.'?action=memberlist">'.$this->txt['members'].'</a></h5>
                <ul>
                <li>' . $bullet. $this->txt['total_members'].': ' , isset($this->modSettings['memberCount']) ? $this->modSettings['memberCount'] : $this->modSettings['totalMembers'] , '</li>
                <li>' . $bullet. $this->txt['tp-latest']. ': <a href="', $this->scripturl, '?action=profile;u=', $this->modSettings['latestMember'], '"><strong>', $this->modSettings['latestRealName'], '</strong></a></li>
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

}

?>
