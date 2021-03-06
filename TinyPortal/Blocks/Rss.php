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

class Rss extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $block['title'] = '<span class="header">' . $block['title'] . '</span>';

    }}}

    function display( $block ) {{{

        echo '<div style="padding: 5px; ' , !empty($this->context['TPortal']['rsswidth']) ? 'max-width: ' . $this->context['TPortal']['rsswidth'] .';' : '' , '" class="middletext">' , TinyPortal\Model\Subs::getInstance()->parseRSS('', $this->context['TPortal']['rss_utf8']) , '</div>';

    }}}

}

?>
