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
namespace TinyPortal\Controller;

use \TinyPortal\Model\Article as TPArticle;
use \TinyPortal\Model\Block as TPBlock;
use \TinyPortal\Model\Database as TPDatabase;
use \TinyPortal\Model\Integrate as TPIntegrate;
use \TinyPortal\Model\Mentions as TPMentions;
use \TinyPortal\Model\Permissions as TPPermissions;
use \TinyPortal\Model\Shout as TPShout;
use \TinyPortal\Model\Subs as TPSubs;
use \TinyPortal\Model\Util as TPUtil;
use \ElkArte\Errors\Errors;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Shout
{

    public function __construct() {{{
        global $context, $txt;

        if(TPSubs::getInstance()->loadLanguage('TPShout') == false) {
            TPSubs::getInstance()->loadLanguage('TPShout', 'english');
        }

        // a switch to make it clear what is "forum" and not
        $context['TPortal']['not_forum'] = true;

        // clear the linktree first
        TPSubs::getInstance()->strip_linktree();
    }}}

    public function action_index() {{{

    }}}

    public function action_shout() {{{

    }}}

    public function action_delete() {{{

    }}}

    public function action_edit() {{{

    }}}

}

?>
