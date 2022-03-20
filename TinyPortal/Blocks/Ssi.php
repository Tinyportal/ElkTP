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

class Ssi extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $block['title'] = '<span class="header">' . $block['title'] . '</span>'; 

    }}}

    public function display( $block ) {{{

        echo '<div style="padding: 5px;" class="smalltext">';
        if($block['body'] == 'toptopics')
            \ssi_topTopics();
        elseif($block['body'] == 'topboards')
            \ssi_topBoards();
        elseif($block['body'] == 'topposters')
            \ssi_topPoster(5);
        elseif($block['body'] == 'topreplies')
            \ssi_topTopicsReplies();
        elseif($block['body'] == 'topviews')
            \ssi_topTopicsViews();
        elseif($block['body'] == 'calendar')
            \ssi_todaysCalendar();

        echo '</div>';

    }}}

    public function admin_setup( &$block ) {{{

    }}}

    public function admin_display( $block ) {{{

		if(!in_array($this->context['TPortal']['blockedit']['body'],array('recentpoll','toppoll','topposters','topboards','topreplies','topviews','calendar'))) {
			$this->context['TPortal']['blockedit']['body']='';
		}

		echo '
			</div><div>';

		echo '
		<hr>
			<dl class="tptitle settings">
				<dt>
					<label for="field_name">'.$this->txt['tp-showssibox'].'</label>
				</dt>
				<dd>
					<input type="radio" id="tp_block_body0" name="tp_block_body" value="" ' , $this->context['TPortal']['blockedit']['body']=='' ? 'checked' : '' , '><label for="tp_block_body0"> ' .$this->txt['tp-none-']. '</label><br>
					<input type="radio" id="tp_block_body1" name="tp_block_body" value="topboards" ' , $this->context['TPortal']['blockedit']['body']=='topboards' ? 'checked' : '' , '><label for="tp_block_body1"> '.$this->txt['tp-ssi-topboards']. '</label><br>
					<input type="radio" id="tp_block_body2" name="tp_block_body" value="topposters" ' , $this->context['TPortal']['blockedit']['body']=='topposters' ? 'checked' : '' , '><label for="tp_block_body2"> '.$this->txt['tp-ssi-topposters']. '</label><br>
					<input type="radio" id="tp_block_body3" name="tp_block_body" value="topreplies" ' , $this->context['TPortal']['blockedit']['body']=='topreplies' ? 'checked' : '' , '><label for="tp_block_body3"> '.$this->txt['tp-ssi-topreplies']. '</label><br>
					<input type="radio" id="tp_block_body4" name="tp_block_body" value="topviews" ' , $this->context['TPortal']['blockedit']['body']=='topviews' ? 'checked' : '' , '><label for="tp_block_body4"> '.$this->txt['tp-ssi-topviews']. '</label><br>
					<input type="radio" id="tp_block_body5" name="tp_block_body" value="calendar" ' , $this->context['TPortal']['blockedit']['body']=='calendar' ? 'checked' : '' , '><label for="tp_block_body5"> '.$this->txt['tp-ssi-calendar']. '</label><br>
				</dd>
			</dl>';

		return true;

    }}}

}

?>
