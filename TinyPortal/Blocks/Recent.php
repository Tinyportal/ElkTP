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

class Recent extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $mp = '<a class="subject"  href="'.$this->scripturl.'?action=recent">'.$block['title'].'</a>';
        if(!empty($block['boards'])) {
            $block['boards'] = explode(',', $block['boards']);
        }

    }}}

    public function display( $block ) {{{

        // is it a number?
        if(!is_numeric($block['total']))
            $block['total']='10';

        // exclude boards
        if (isset($block['boards']) && $block['include'] == 0) {
            $exclude_boards = $block['boards'];
        }
        else {
            // leave out the recycle board, if any
            if(isset($this->modSettings['recycle_board']) && $this->modSettings['recycle_enable'] = 1 ) {
                $bb = array($this->modSettings['recycle_board']);
            }
            $exclude_boards = $bb;
        }

        // include boards
        if (isset($block['boards']) && !$block['include'] == 0) {
            $include_boards = $block['boards'];
        }
        else {
            $include_boards = null;
        }

        $what = \ssi_recentTopics($num_recent = $block['total'] , $exclude_boards,  $include_boards, $output_method = 'array');

        if($block['avatar'] == 0) {
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

            if(!empty($member_ids)) {
                $avatars = \TinyPortal\Model\Subs::getInstance()->getAvatars($member_ids);
            }
            else {
                $avatars = array();
            }

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

    public function admin_setup( &$block ) {{{

		parent::admin_setup($block);

		$default = array(
			'avatar'	=> 0,
			'boards'	=> '',
			'include'	=> 0,
			'total'		=> 10,
		);

		if(empty($block['settings'])) {
			$block += $default;
		}

		// We also need to check that boards isn't empty
		if(empty($block['boards'])) {
			$block['boards'] = '';
		}

    }}}

	public function admin_display( $block ) {{{

		echo '
			<hr>
			<dl class="tptitle settings">
				<dt>
					<label for="tp_block_set_total">'.$this->txt['tp-numberofrecenttopics'].'</label></dt>
				<dd>
					<input type="number" id="tp_block_set_total" name="tp_block_set_total" value="' .$block['total']. '" style="width: 6em" min="1">
				</dd>
				<dt>
					<label for="tp_block_set_boards">'.$this->txt['tp-recentboards'].'</label></dt>
				<dd>
					<input type="text" id="tp_block_set_boards" name="tp_block_set_boards" value="' , $block['boards'] ,'" size="20" pattern="[0-9,]+">
				</dd>';

		echo '
				<dt>
					<label for="field_name">'.$this->txt['tp-recentincexc'].'</label>
				</dt>
				<dd>
					<input type="radio" id="tp_block_includein" name="tp_block_set_include" value="1" ' , ($block['include']=='1' || $block['include']=='') ? ' checked' : '' ,'> <label for="tp_block_includein">'.$this->txt['tp-recentinboard'].'</label><br>
					<input type="radio" id="tp_block_includeex" name="tp_block_set_include" value="0" ' , $block['include']=='0' ? 'checked' : '' ,'> <label for="tp_block_includeex">'.$this->txt['tp-recentexboard'].'</label>
				</dd>
				<dt>
					<label for="field_name">' . $this->txt['tp-rssblock-showavatar'].'</label>
				</dt>
				<dd>
					<input type="radio" name="tp_block_set_avatar" value="1" ' , ($block['avatar']=='1' || $block['avatar']=='') ? ' checked' : '' ,'>'.$this->txt['tp-yes'].'
					<input type="radio" name="tp_block_set_avatar" value="0" ' , $block['avatar']=='0' ? ' checked' : '' ,'>'.$this->txt['tp-no'].'
				</dd>
			</dl>';


		return true;

    }}}

}
?>
