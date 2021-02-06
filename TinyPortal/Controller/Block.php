<?php
/**
 * @package TinyPortal
 * @version 1.0.0 RC2
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

        if(\loadLanguage('TPmodules') == false) {
            \loadLanguage('TPmodules', 'english');
        }

        if(\loadLanguage('TPortalAdmin') == false) {
            \loadLanguage('TPortalAdmin', 'english');
        }

        // a switch to make it clear what is "forum" and not
        $context['TPortal']['not_forum'] = true;

        // clear the linktree first
        TPSubs::getInstance()->strip_linktree();
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
                // some tests to minimize sql calls
                if($row['type'] == TP_BLOCK_THEMEBOX) {
                    $test_themebox = true;
                }
                elseif($row['type'] == TP_BLOCK_ARTICLEBOX) {
                    $test_articlebox = true;
                    if(is_numeric($row['body'])) {
                        $fetch_articles[] = $row['body'];
                    }
                }
                elseif($row['type'] == TP_BLOCK_CATEGORYBOX) {
                    $test_catbox = true;
                    if(is_numeric($row['body'])) {
                        $fetch_article_titles[] = $row['body'];
                    }
                }
                elseif($row['type'] == TP_BLOCK_SHOUTBOX) {
                    call_integration_hook('integrate_tp_shoutbox', array(&$row));
                }

                // decode the block settings
                $set        = json_decode($row['settings'], true);
                $can_edit   = !empty($row['editgroups']) ? TPSubs::getInstance()->get_perm($row['editgroups'], '') : false;
                $can_manage = allowedTo('tp_blocks');
                if($can_manage) {
                    $can_edit = false;
                }
                $blocks[$panels[$row['bar']]][$count[$panels[$row['bar']]]] = array(
                    'frame'     => $row['frame'],
                    'title'     => strip_tags($row['title'], '<center>'),
                    'type'      => $tpBlock->getBlockType($row['type']),
                    'body'      => $row['body'],
                    'visible'   => $row['visible'],
                    'settings'  => $row['settings'],
                    'var1'      => $set['var1'],
                    'var2'      => $set['var2'],
                    'var3'      => $set['var3'],
                    'var4'      => $set['var4'],
                    'var5'      => $set['var5'],
                    'id'        => $row['id'],
                    'lang'      => $row['lang'],
                    'display'   => $row['display'],
                    'can_edit'  => $can_edit,
                    'can_manage' => $can_manage,
                );
                $count[$panels[$row['bar']]]++;
            }
        }

        // if a block displays an article
        if(isset($test_articlebox)) {
            $context['TPortal']['blockarticles'] = array();
            $tpArticle  = new TPArticle();
            $articles   = $tpArticle->getArticle($fetch_articles);
            if(is_array($articles)) {
                foreach($articles as $article) {
                    // allowed and all is well, go on with it.
                    $context['TPortal']['blockarticles'][$article['id']] = $article;
                    // setup the avatar code
                    if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize') {
                        $avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
                        $avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
                    }
                    else {
                        $avatar_width = '';
                        $avatar_height = '';
                    }
                    $context['TPortal']['blockarticles'][$article['id']]['avatar'] = determineAvatar( array(
                                'avatar'            => $article['avatar'],
                                'email_address'     => $article['email_address'],
                                'filename'          => !empty($article['filename']) ? $article['filename'] : '',
                                'id_attach'         => $article['id_attach'],
                                'attachment_type'   => $article['attachment_type'],
                            )
                    )['image'];
                    // sort out the options
                    $context['TPortal']['blockarticles'][$article['id']]['visual_options'] = array();
                    // since these are inside blocks, some stuff has to be left out
                    $context['TPortal']['blockarticles'][$article['id']]['frame'] = 'none';
                }
            }
        }

        // any cat listings from blocks?
        if(isset($test_catbox)) {
            $tpArticle  = new TPArticle();
            $categories = $tpArticle->getArticlesInCategory($fetch_article_titles, false, true);
            if (!isset($context['TPortal']['blockarticle_titles'])) {
                $context['TPortal']['blockarticle_titles'] = array();
            }
            if(is_array($categories)) {
                foreach($categories as $row) {
                    if(empty($row['author'])) {
                        global $memberContext;
                        // Load their context data.
                        if(!array_key_exists('admin_features', $context)) {
                            $context['admin_features']  = array();
                            $adminFeatures              = true;
                        }
                        else {
                            $adminFeatures              = false;
                        }

                        \loadMemberData($row['author_id'], false, 'normal');
                        \loadMemberContext($row['author_id']);

                        if($adminFeatures == true) {
                            unset($context['admin_features']);
                        }
                        $row['real_name'] = $memberContext[$row['author_id']]['username'];
                    }
                    else {
                        $row['real_name'] = $row['author'];
                    }
                    $context['TPortal']['blockarticle_titles'][$row['category']][$row['date'].'_'.$row['id']] = array(
                        'id' => $row['id'],
                        'subject' => $row['subject'],
                        'shortname' => $row['shortname']!='' ?$row['shortname'] : $row['id'] ,
                        'category' => $row['category'],
                        'poster' => '<a href="'.$scripturl.'?action=profile;u='.$row['author_id'].'">'.$row['real_name'].'</a>',
                    );
                }
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
