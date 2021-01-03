<?php
/**
 * @package TinyPortal
 * @version 1.0.0
 * @author TinyPortal - http://www.tinyportal.net
 * @license BSD 3.0 http://opensource.org/licenses/BSD-3-Clause/
 *
 * Copyright (C) 2020 - The TinyPortal Team
 *
 */
use \TinyPortal\Article as TPArticle;
use \TinyPortal\Block as TPBlock;
use \TinyPortal\Integrate as TPIntegrate;
use \TinyPortal\Mentions as TPMentions;
use \TinyPortal\Util as TPUtil;
use ElkArte\Errors\Errors;
use ElkArte\sources\Frontpage_Interface;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

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
        global $context, $txt;
      
		\loadLanguage('TPortal');
        \loadTemplate('TPortal');

        $action = TPUtil::filter('action', 'get', 'string');
        if($action == 'tportal') {
            $subAction  = TPUtil::filter('sa', 'get', 'string');
            if($subAction == false) {
                fatal_error($txt['tp-no-sa-url'], false);
            }

            $subActions = array (
                'credits'           => array('TPhelp.php', 'TPCredits'      , array()),
                'updatelog'         => array('TPSubs.php', 'TPUpdateLog'    , array()),
            );

            call_integration_hook('integrate_tp_pre_subactions', array(&$subActions));

            if(!array_key_exists($subAction, $subActions)) {
                fatal_error($txt['tp-no-sa-list'], false);
            }

            $context['TPortal']['subaction'] = $subAction;
            // If it exists in our new subactions array load it
            if(!empty($subAction) && array_key_exists($subAction, $subActions)) {
                if (!empty($subActions[$subAction][0])) {
                    require_once(SOURCEDIR . '/' . $subActions[$subAction][0]);
                }

                call_user_func_array($subActions[$subAction][1], $subActions[$subAction][2]);
            }

            call_integration_hook('integrate_tp_post_subactions');
        }

    }}}

    public function action_portal() {{{

        $subAction  = TPUtil::filter('sa', 'get', 'string');
        if($subAction == false) {
            Errors::instance()->fatal_error($txt['tp-no-sa-url'], false);
        }

    }}}

    public function trackStats($stats = array()) {{{


    }}}
}

?>
