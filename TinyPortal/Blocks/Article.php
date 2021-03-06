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

class Article extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $block['title'] = '<span class="header">' . $block['title'] . '</span>';
        $this->context['TPortal']['blockarticle'] = $block['body'];

    }}}

    public function display( $block ) {{{

        if(isset($this->context['TPortal']['blockarticles'][$this->context['TPortal']['blockarticle']])) {
		    echo '<div class="block_article">';
            if(!empty($this->context['TPortal']['blockarticles'][$this->context['TPortal']['blockarticle']]['template'])) {
                \TinyPortal\Model\Subs::getInstance()->render_template($this->context['TPortal']['blockarticles'][$this->context['TPortal']['blockarticle']]['template']);
            }
            else {
                \TinyPortal\Model\Subs::getInstance()->render_template(blockarticle_renders());
            }
            echo '</div>';
        }

    }}}

}

?>
