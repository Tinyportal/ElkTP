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

}

?>
