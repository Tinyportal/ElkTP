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

class Search extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $block['title'] = '<a class="subject" href="'.$this->scripturl.'?action=search">'.$block['title'].'</a>';

    }}}

    public function display( $block ) {{{

        echo '
        <form accept-charset="', 'UTF-8', '" action="', $this->scripturl, '?action=search;sa=results" method="post" style="padding: 0; text-align: center; margin: 0; ">
            <input type="text" class="block_search" name="search" value="" />
            <input type="submit" name="submit" value="', $this->txt['search'], '" class="block_search_submit button_submit" /><br>
            <br><span class="smalltext"><a href="', $this->scripturl, '?action=search;advanced">', $this->txt['search_advanced'], '</a></span>
            <input type="hidden" name="advanced" value="0" />
        </form>';

    }}}

    public function admin_setup( &$block ) {{{

    }}}

    public function admin_display( $block ) {{{

		return false;

    }}}

}

?>
