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
namespace TinyPortal\Blocks;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Recent extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $mp = '<a class="subject"  href="'.$this->scripturl.'?action=recent">'.$block['title'].'</a>';
        $this->context['TPortal']['recentboxnum'] = $block['body'];
        $this->context['TPortal']['useavatar'] = $block['var1'];
        $this->context['TPortal']['boardmode'] = $block['var3'];
        if($block['var1'] == '') {
            $this->context['TPortal']['useavatar'] = 1;
        }
        if(!empty($block['var2'])) {
            $this->context['TPortal']['recentboards'] = explode(',', $block['var2']);
        }

    }}}

    public function display( $block ) {{{

        // is it a number?
        if(!is_numeric($this->context['TPortal']['recentboxnum']))
            $this->context['TPortal']['recentboxnum']='10';

        // exclude boards
        if (isset($this->context['TPortal']['recentboards']) && $this->context['TPortal']['boardmode'] == 0)
            $exclude_boards = $this->context['TPortal']['recentboards'];
        else {
            // leave out the recycle board, if any
            if(isset($this->modSettings['recycle_board']) && $this->modSettings['recycle_enable'] = 1 )
                $bb = array($this->modSettings['recycle_board']);
            $exclude_boards = $bb;
        }

        // include boards
        if (isset($this->context['TPortal']['recentboards']) && !$this->context['TPortal']['boardmode'] == 0)
            $include_boards = $this->context['TPortal']['recentboards'];
        else
            $include_boards = null;

        $what = \ssi_recentTopics($num_recent = $this->context['TPortal']['recentboxnum'] , $exclude_boards,  $include_boards, $output_method = 'array');

        if($this->context['TPortal']['useavatar'] == 0) {
            // Output the topics
            echo '
                <ul class="recent_topics" style="' , isset($this->context['TPortal']['recentboxscroll']) && $this->context['TPortal']['recentboxscroll'] == 1 ? 'overflow: auto; height: 20ex;' : '' , 'margin: 0; padding: 0;">';
            $coun = 1;
            foreach($what as $wi => $w) {
                echo '
                    <li' , $coun<count($what) ? '' : ' style="border: none; margin-bottom: 0;padding-bottom: 0;"'  , '>';
                if(!empty($w['is_new'])) {
                    echo ' <a href="' . $this->scripturl . '?topic=' . $w['topic'] . '.msg' . $w['new_from'] . ';topicseen#new" rel="nofollow" class="new_posts" style="margin:0px;">' . $this->txt['new'] . '</a> ';
                }
                else {
                    echo ' <a href="' . $this->scripturl . '?topic=' . $w['topic'] . '.msg' . $w['new_from'] . ';topicseen" rel="nofollow" class="posts" style="margin:0px;"></a> ';
                }
                echo '
                    <a href="' . $w['href'] . '" title="' . $w['subject'] . '">' . $w['short_subject'] . '</a>
                    ', $this->txt['by'], ' <b>', $w['poster']['link'],'</b> ';
                echo '<br><span class="smalltext">['.$w['time'].']</span>
                    </li>';
                $coun++;
            }
            echo '
                </ul>';
        }
        else {
            $member_ids = array();
            foreach($what as $wi => $w) {
                $member_ids[] = $w['poster']['id'];
            }

            if(!empty($member_ids))
                $avatars = \TinyPortal\Model\Subs::getInstance()->getAvatars($member_ids);
            else
                $avatars = array();

            // Output the topics
            $coun = 1;
            echo '
                <ul class="recent_topics" style="' , isset($this->context['TPortal']['recentboxscroll']) && $this->context['TPortal']['recentboxscroll']==1 ? 'overflow: auto; height: 20ex;' : '' , 'margin: 0; padding: 0;">';
            foreach($what as $wi => $w) {
                echo '
                    <li' , $coun<count($what) ? '' : ' style="border: none; margin-bottom: 0;padding-bottom: 0;"'  , '>';
                if(!empty($w['is_new'])) {
                    echo ' <a href="' . $this->scripturl . '?topic=' . $w['topic'] . '.msg' . $w['new_from'] . ';topicseen#new" rel="nofollow" class="new_posts" style="margin:0px;">' . $this->txt['new'] . '</a> ';
                }
                else {
                    echo ' <a href="' . $this->scripturl . '?topic=' . $w['topic'] . '.msg' . $w['new_from'] . ';topicseen" rel="nofollow" class="posts" style="margin:0px;"></a> ';
                }
                echo '
                    <span class="tpavatar"><a href="' . $this->scripturl. '?action=profile;u=' . $w['poster']['id'] . '">' , empty($avatars[$w['poster']['id']]) ? '<img src="' . $this->settings['tp_images_url'] . '/TPguest.png" alt="" />' : $avatars[$w['poster']['id']] , '</a></span><a href="'.$w['href'].'">' . $w['short_subject'].'</a>
                    ', $this->txt['by'], ' <b>', $w['poster']['link'],'</b> ';
                echo '<br><span class="smalltext">['.$w['time'].']</span>
                    </li>';
                $coun++;
            }
            echo '
                </ul>';
        }


    }}}

}

?>
