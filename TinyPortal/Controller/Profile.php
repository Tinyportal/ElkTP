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
use \TinyPortal\Model\Subs as TPSubs;
use \TinyPortal\Model\Util as TPUtil;
use \ElkArte\Errors\Errors;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Profile
{

    public function __construct() {{{
    
    }}}

    public function pre_dispatch() {{{

    }}}

    public function summary($member_id = null) {{{
        global $txt, $context;

        if(is_null($member_id)) {
            $member_id = TPUtil::filter('u', 'get', 'int');
        }

        \theme()->getTemplates()->load('TPprofile');
        $context['page_title']      = $txt['tpsummary'];
        $context['sub_template']    = 'tp_summary';
        TPSubs::getInstance()->profile_summary($member_id);

    }}}
    
    public function articles($member_id = null) {{{
        global $context;

        if(is_null($member_id)) {
            $member_id = TPUtil::filter('u', 'get', 'int');
        }

        \theme()->getTemplates()->load('TPprofile');
        $context['sub_template']    = 'tp_articles';
        TPSubs::getInstance()->profile_articles($member_id);

    }}}
}

?>
