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

class Block
{

    public function __construct() {{{
        global $context, $txt;

        if(TPSubs::getInstance()->loadLanguage('TPmodules') == false) {
            TPSubs::getInstance()->loadLanguage('TPmodules', 'english');
        }

        if(TPSubs::getInstance()->loadLanguage('TPortalAdmin') == false) {
            TPSubs::getInstance()->loadLanguage('TPortalAdmin', 'english');
        }

        // a switch to make it clear what is "forum" and not
        $context['TPortal']['not_forum'] = true;

        // clear the linktree first
        TPSubs::getInstance()->strip_linktree();

		//parent::__construct(new \ElkArte\EventManager());
	}}}

    public static function loadBlocks() {{{
        global $context, $scripturl, $user_info, $modSettings;

        $tpBlock    = TPBlock::getInstance();

        $now = time();
        // setup the containers
        $blocks = $tpBlock->getBlockType();

        $context['TPortal']['hide_frontbar_forum'] = 0;

        $fetch_articles = array();
        $fetch_article_titles = array();

        $count  = array_flip($tpBlock->getBlockPanel());
        foreach($count as $k => $v) {
            $count[$k] = 0;
        }

        $panels             = $tpBlock->getBlockBar();
        $availableBlocks    = $tpBlock->getBlockPermissions();
        if (is_array($availableBlocks) && count($availableBlocks)) {
            foreach($availableBlocks as $row) {
                $blockClass = '\TinyPortal\Blocks\\'.ucfirst($tpBlock->getBlockType($row['type']));
                if(class_exists($blockClass)) {
                    (new $blockClass)->prepare($row);
                }
                else {
                    \Errors::instance()->log_error('Depreciated: block: ' . $tpBlock->getBlockType($row['type']), 'depreciated');
                    continue;
                }

                // decode the block settings
                $set        = json_decode($row['settings'], true) ?? array();
                $can_edit   = !empty($row['editgroups']) ? TPSubs::getInstance()->perm($row['editgroups'], '') : false;
                $can_manage = allowedTo('tp_blocks');
                if($can_manage) {
                    $can_edit = false;
                }
                $blocks[$panels[$row['bar']]][$count[$panels[$row['bar']]]] = $set + array(
                    'frame'     => $row['frame'],
                    'title'     => strip_tags($row['title'], '<center>'),
                    'type'      => $tpBlock->getBlockType($row['type']),
                    'body'      => $row['body'],
                    'visible'   => $row['visible'],
                    'settings'  => $row['settings'],
                    'id'        => $row['id'],
                    'lang'      => $row['lang'],
                    'display'   => $row['display'],
                    'can_edit'  => $can_edit,
                    'can_manage' => $can_manage,
                );
                $count[$panels[$row['bar']]]++;
            }
        }

        // for tpadmin
        $context['TPortal']['adminleftpanel']   = $context['TPortal']['leftpanel'];
        $context['TPortal']['adminrightpanel']  = $context['TPortal']['rightpanel'];
        $context['TPortal']['admincenterpanel'] = $context['TPortal']['centerpanel'];
        $context['TPortal']['adminbottompanel'] = $context['TPortal']['bottompanel'];
        $context['TPortal']['admintoppanel']    = $context['TPortal']['toppanel'];
        $context['TPortal']['adminlowerpanel']  = $context['TPortal']['lowerpanel'];

        // if admin specifies no blocks, no blocks are shown! likewise, if in admin or tpadmin screen, turn off blocks
        if (in_array($context['TPortal']['action'], array('moderate', 'theme', 'tpadmin', 'admin', 'ban', 'boardrecount', 'cleanperms', 'detailedversion', 'dumpdb', 'featuresettings', 'featuresettings2', 'findmember', 'maintain', 'manageattachments', 'manageboards', 'managecalendar', 'managesearch', 'membergroups', 'modlog', 'news', 'optimizetables', 'packageget', 'packages', 'permissions', 'pgdownload', 'postsettings', 'regcenter', 'repairboards', 'reports', 'serversettings', 'serversettings2', 'smileys', 'viewErrorLog', 'viewmembers'))) {
            $in_admin = true;
        }

        if(($context['user']['is_admin'] && isset($_GET['noblocks'])) || ($context['TPortal']['hidebars_admin_only']=='1' && isset($in_admin))) {
            TPSubs::getInstance()->hidebars();
        }

        // check the panels
        foreach($panels as $p => $panel) {
            // any blocks at all?
            if($count[$panel] < 1) {
                $context['TPortal'][$panel.'panel'] = 0;
            }
            // check the hide setting
            if(!isset($context['TPortal']['not_forum']) && $context['TPortal']['hide_' . $panel . 'bar_forum']==1) {
                TPSubs::getInstance()->hidebars($panels);
            }
        }

        $context['TPortal']['blocks'] = $blocks;

    }}}

}

?>
