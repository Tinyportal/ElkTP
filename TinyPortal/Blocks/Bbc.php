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

class Bbc extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $block['title'] = '<span class="header">' . $block['title'] . '</span>';

    }}}

    public function display( $block ) {{{

        if(!empty($block['body'])) {
            echo \parse_bbc($block['body']);
        }

    }}}

}

?>