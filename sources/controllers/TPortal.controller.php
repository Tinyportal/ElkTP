<?php
/**
 * @package TinyPortal
 * @version 2.1.0
 * @author IchBin - http://www.tinyportal.net
 * @founder Bloc
 * @license MPL 2.0
 *
 * The contents of this file are subject to the Mozilla Public License Version 2.0
 * (the "License"); you may not use this package except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Copyright (C) 2020 - The TinyPortal Team
 *
 */
use \TinyPortal\Article as TPArticle;
use \TinyPortal\Block as TPBlock;
use \TinyPortal\Integrate as TPIntegrate;
use \TinyPortal\Mentions as TPMentions;
use \TinyPortal\Util as TPUtil;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

use ElkArte\sources\Frontpage_Interface;

class TPortal_Controller extends Action_Controller implements Frontpage_Interface
{

    public static function canFrontPage() {{{
        return true;
    }}}

    public static function frontPageOptions() {{{
        return true;
	}}}

    public static function frontPageHook(&$default_action) {{{

        // View the portal front page
        $file       = __FILE__;
        $controller = 'TPortal_Controller';
        $function   = 'action_index';
        // Something article-ish, then set the new action
        if (isset($file, $function)) {
            $default_action = array(
                'file' => $file,
                'controller' => isset($controller) ? $controller : null,
                'function' => $function
            );
        }

    }}}

	public static function validateFrontPageOptions($post) {{{
        return true;
    }}}

    public function action_index() {{{


    }}}

}

?>
