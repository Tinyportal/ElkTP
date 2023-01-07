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
            echo \TinyPortal\Model\Subs::getInstance()->parse_bbc($block['body']);
        }

    }}}

    public function admin_setup( &$block ) {{{

		parent::admin_setup($block);

    }}}

	public function admin_display( $block ) {{{

		echo '</div><hr><div>';
		\TinyPortal\Model\Subs::getInstance()->bbcbox($this->context['TPortal']['editor_id']);	

		return true;

    }}}


}

?>
