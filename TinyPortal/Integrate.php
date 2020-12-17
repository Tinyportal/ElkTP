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
namespace TinyPortal;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Integrate 
{

    public static function hookPreLoad() {{{

        // We need to load our autoloader outside of the main function    
        if(!defined('ELK_BACKWARDS_COMPAT')) {
            define('ELK_BACKWARDS_COMPAT', true);
            self::setup_db_backwards_compat();
            spl_autoload_register('\TinyPortal\Integrate::TPortalAutoLoadClass');
        }

        $hooks = array (
            'SSI'                               => 'SOURCEDIR/TPSSI.php|ssi_TPIntegrate',
            'current_action'                    => '\TinyPortal\Integrate::hookCurrentAction',
            'load_permissions'                  => '\TinyPortal\Integrate::hookPermissions',
            'load_illegal_guest_permissions'    => '\TinyPortal\Integrate::hookIllegalPermissions',
            'buffer'                            => '\TinyPortal\Integrate::hookBuffer',
            'menu_buttons'                      => '\TinyPortal\Integrate::hookMenuButtons',
            'display_buttons'                   => '\TinyPortal\Integrate::hookDisplayButton',
            'actions'                           => '\TinyPortal\Integrate::hookActions',
            'whos_online'                       => '\TinyPortal\Integrate::hookWhosOnline',
            'pre_log_stats'                     => '\TinyPortal\Integrate::hookPreLogStats',
            'pre_profile_areas'                 => '\TinyPortal\Integrate::hookProfileArea',
            'pre_load_theme'                    => '\TinyPortal\Integrate::hookLoadTheme',
            'redirect'                          => '\TinyPortal\Integrate::hookRedirect',
            'action_frontpage'                  => '\TinyPortal\Integrate::hookFrontPage',
            'tp_pre_subactions'                 => array ( 
                'SOURCEDIR/TPArticle.php|TPArticleActions',
                'SOURCEDIR/TPSearch.php|TPSearchActions',
                'SOURCEDIR/TPBlock.php|TPBlockActions',
                'SOURCEDIR/TPdlmanager.php|TPDownloadActions',
                'SOURCEDIR/TPcommon.php|TPCommonActions',
            ),
            'tp_post_subactions'                => array ( 
            ),           
            'tp_post_init'                      => array (
                'SOURCEDIR/TPBlock.php|getBlocks',
                'SOURCEDIR/TPShout.php|TPShoutLoad',
            ),
            'tp_admin_areas'                    => array (
                'SOURCEDIR/TPdlmanager.php|TPDownloadAdminAreas',
                'SOURCEDIR/TPShout.php|TPShoutAdminAreas',
                'SOURCEDIR/TPListImages.php|TPListImageAdminAreas',
            ),
            'tp_shoutbox'                       => array (
                'SOURCEDIR/TPShout.php|TPShoutBlock',
            ),
            'tp_block'                          => array (
            ),
            'tp_pre_admin_subactions'           => array ( 
                'SOURCEDIR/TPBlock.php|TPBlockAdminActions',
                'SOURCEDIR/TPShout.php|TPShoutAdminActions',
                'SOURCEDIR/TPListImages.php|TPListImageAdminActions',
            ),
        );

		foreach ($hooks as $hook => $callable) {
            if(is_array($callable)) {
                foreach($callable as $call ) {
                    if((strpos($call, '|') !== false) ) {
                        $tmp = explode('|', $call);
	                    add_integration_function('integrate_' . $hook, $tmp[1], $tmp[0], false);
                    }
                    else {
                        add_integration_function('integrate_' . $hook, $call, __FILE__, false);
                    }
                }
            }
            else {
                if((strpos($callable, '|') !== false) ) {
                    $tmp = explode('|', $callable);
                    add_integration_function('integrate_' . $hook, $tmp[1], $tmp[0], false);
                }
                else {
                    add_integration_function('integrate_' . $hook, $callable, __FILE__, false);
                }
            }
		}
        
        }}}

    public static function hookFrontPage(&$defaultAction) {{{
        global $modSettings;

        // Should check TinyPortal is enabled..
        $modSettings['front_page']  = 'TPortal_Controller';

        return;
    }}}

    public static function TPortalAutoLoadClass($className) {{{

        $classPrefix    = mb_substr($className, 0, 10);

        if( 'TinyPortal' !== $classPrefix ) {
            return;
        }

        $className  = str_replace('\\', '/', $className);
        $classFile  = BOARDDIR . '/' . $className . '.php';

        if ( file_exists( $classFile ) ) {
            require_once($classFile);
        }

    }}}

    public static function setup_db_backwards_compat() {{{
        global $db_type, $context;

        if($db_type == 'postgresql') {
            define('TP_PGSQL', true);
        }
        else {
            define('TP_PGSQL', false);
        }

        // Set this up for everything that TinyPortal needs
        $context['TPortal']                     = array();
        // Set default values
        $context['TPortal']['is_front']         = false;
        $context['TPortal']['is_frontpage']     = false;
        $context['TPortal']['action']           = '';
        $context['TPortal']['front_type']       = '';
        $context['TPortal']['frontblock_type']  = '';

    }}}

    public static function hookPermissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions) {{{
    
        $permissionList['membergroup'] = array_merge(
            array(
                'tp_settings' => array(false, 'tp', 'tp'),
                'tp_blocks' => array(false, 'tp', 'tp'),
                'tp_articles' => array(false, 'tp', 'tp'),
                'tp_submithtml' => array(false, 'tp', 'tp'),
                'tp_submitbbc' => array(false, 'tp', 'tp'),
                'tp_editownarticle' => array(false, 'tp', 'tp'),
				'tp_alwaysapproved' => array(false, 'tp', 'tp'),
                'tp_artcomment' => array(false, 'tp', 'tp'),
                'tp_can_admin_shout' => array(false, 'tp', 'tp'),
                'tp_can_shout' => array(false, 'tp', 'tp'),
                'tp_dlmanager' => array(false, 'tp', 'tp'),
                'tp_dlupload' => array(false, 'tp', 'tp'),
                'tp_dlcreatetopic' => array(false, 'tp', 'tp'),
                'tp_can_list_images' => array(false, 'tp', 'tp'),
            ),
            $permissionList['membergroup']
        );

    }}}

    // Adds TP copyright in the buffer so we don't have to edit an ELK file
    public static function hookBuffer($buffer) {{{
        global $context, $scripturl, $txt, $boardurl;
        
        // Dynamic body ID
        if (isset($context['TPortal']) && $context['TPortal']['action'] == 'profile') {
            $bodyid = "profilepage";
        } elseif (isset($context['TPortal']) && $context['TPortal']['action'] == 'pm') {
            $bodyid = "pmpage";
        } elseif (isset($context['TPortal']) && $context['TPortal']['action'] == 'calendar') {
            $bodyid = "calendarpage";
        } elseif (isset($context['TPortal']) && $context['TPortal']['action'] == 'mlist') {
            $bodyid = "mlistpage";
        } elseif (isset($context['TPortal']) && in_array($context['TPortal']['action'], array('search', 'search2'))) {
            $bodyid = "searchpage";
        } elseif (isset($context['TPortal']) && $context['TPortal']['action'] == 'forum') {
            $bodyid = "forumpage";
        } elseif (isset($_GET['board']) && !isset($_GET['topic'])) {
            $bodyid = "boardpage";
        } elseif (isset($_GET['board']) && isset($_GET['topic'])) {
              $bodyid = "topicpage";
        } elseif (isset($_GET['page'])) {
            $bodyid = "page";
        } elseif (isset($_GET['cat'])) {
            $bodyid = "catpage";
        } elseif (isset($context['TPortal']) && $context['TPortal']['is_frontpage']) {
            $bodyid = "frontpage";
        } else {
            $bodyid = "tpbody";
        }

        // Dynamic body classes
        if (isset($_GET['board']) && !isset($_GET['topic'])) {
            $bclass =  "boardpage board" . $_GET['board'];
        } elseif (isset($_GET['board']) && isset($_GET['topic'])) {
            $bclass =  "boardpage board" . $_GET['board'] . " " . "topicpage topic" . $_GET['topic'];
        } elseif (isset($_GET['page'])) {
            $bclass =  "page" . $_GET['page'];
        } elseif (isset($_GET['cat'])) {
            $bclass =  "cat" . $_GET['cat'];
        } else {
            $bclass =  "tpcontainer";
        }


        $string = '<a target="_blank" href="https://www.tinyportal.net" title="TinyPortal">TinyPortal 2.1.0</a> &copy; <a href="' . $scripturl . '?action=tportal;sa=credits" title="Credits">2005-2020</a>';

        if (ELK == 'SSI' || empty($context['template_layers']) || (defined('WIRELESS') && WIRELESS ) || strpos($buffer, $string) !== false) {
            return $buffer;
        }

        $find = array(
            '<body>',
            'class="copywrite"',
        );
        $replace = array(
            '<body id="' . $bodyid . '" class="' . $bclass . '">',
            'class="copywrite" style="line-height: 1;"',
        );

        if (!in_array($context['current_action'], array('post', 'post2'))) {
            $finds[] = '[cutoff]';
            $replaces[] = '';
        }

        $buffer = str_replace($find, $replace, $buffer);

        $tmp    = isset($txt['tp-tphelp']) ? $txt['tp-tphelp'] : 'Help';
        $find   = '<a href="'.$scripturl.'?action=help">'.$txt['help'].'</a>';
        $replace= '<a href="https://www.tinyportal.net/docs/" target=_blank>'.$tmp.'</a>';
        $buffer = str_replace($find, $replace.' | '.$find, $buffer);
 
        $tmpurl = parse_url($boardurl, PHP_URL_HOST);
        if(!empty($context['TPortal']['copyrightremoval']) && (sha1('TinyPortal'.$tmpurl) == $context['TPortal']['copyrightremoval'])) {
            return $buffer;
        }
        else {
            $find       = '//www.simplemachines.org" title="Simple Machines" target="_blank" class="new_win">Simple Machines</a>';
            $replace    = '//www.simplemachines.org" title="Simple Machines" target="_blank" class="new_win">Simple Machines</a><br />' . $string;
            $buffer     = str_replace($find, $replace, $buffer);
        }

        if (strpos($buffer, $string) === false) {
            $string = '<div style="text-align: center; width: 100%; font-size: x-small; margin-bottom: 5px;">' . $string . '</div></body></html>';
            $buffer = preg_replace('~</body>\s*</html>~', $string, $buffer);
        }

        return $buffer;
    }}}

    public static function hookIllegalPermissions() {{{
        global $context;

        if (empty($context['non_guest_permissions']))
            $context['non_guest_permissions'] = array();

        $tp_illegal_perms = array(
            'tp_settings',
            'tp_blocks',
            'tp_articles',
            'tp_submithtml',
            'tp_submitbbc',
            'tp_editownarticle',
			'tp_alwaysapproved',
            'tp_artcomment',
            'tp_can_admin_shout',
            'tp_can_shout',
            'tp_dlmanager',
            'tp_dlupload',
            'tp_dlcreatetopic',
            'tp_can_list_images',
        );
        $context['non_guest_permissions'] = array_merge($context['non_guest_permissions'], $tp_illegal_perms);
    }}}

    public static function hookCurrentAction(&$currentAction) {{{

        // Rewrite the current action for the home page
        if( ($currentAction == 'home') && (empty($_REQUEST['action'])) ) {
            $currentAction = 'base';
        } 

    }}}

    public static function hookMenuButtons(&$buttons) {{{
        global $context, $scripturl, $txt, $boardurl;

        // If ELK throws a fatal_error TP is not loaded. So don't even worry about menu items.
        if(!isset($context['TPortal']) || isset($context['uninstalling'])) {
            return;
        }

		\loadLanguage('TPortal');

        $version = Admin::getInstance()->getSetting('version');

        $buttons = \elk_array_insert($buttons, 'home', array (
            'base' => array(
                'title' 	    => $txt['tp-home'],
                'href' 		    => $boardurl,
                'data-icon'     => 'i-home',
                'show'          => true,
                'action_hook' 	=> true,
                ),
            )
        );

        // Change the home icon to something else and rewrite the standard action
        $buttons['home']['data-icon'] = 'i-users';
        $buttons['home']['href']      = $scripturl . '?action=forum';

		if(\allowedTo('tp_settings') || \allowedTo('tp_articles') || \allowedTo('tp_blocks') || \allowedTo('tp_can_list_images')) {
			$context['html_headers'] .= '
			<style>
				.i-newspaper::before {
					content: url("data:image/svg+xml,%3C!-- Generated by IcoMoon.io --%3E%3Csvg version=\'1.1\' xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 32 32\'%3E%3Ctitle%3Enewspaper%3C/title%3E%3Cpath d=\'M28 8v-4h-28v22c0 1.105 0.895 2 2 2h27c1.657 0 3-1.343 3-3v-17h-4zM26 26h-24v-20h24v20zM4 10h20v2h-20zM16 14h8v2h-8zM16 18h8v2h-8zM16 22h6v2h-6zM4 14h10v10h-10z\'%3E%3C/path%3E%3C/svg%3E%0A");
				}
			</style>';			
			
			$buttons = \elk_array_insert($buttons, 'calendar', array (
				'tpadmin' => array(
					'title' 	    => $txt['tp-tphelp'],
					'href' 			=> $scripturl.'?action=tpadmin',
					'data-icon'     => 'i-newspaper',
					'show' 			=> Permissions::checkAdminAreas(),
					'sub_buttons' 	=> Permissions::getButtons(),
					'action_hook' 	=> true,
				),
			), 'after');
		}

    }}}

    public static function hookProfileArea(&$profile_areas) {{{
        global $txt, $context;
        
        $profile_areas['tp'] = array(
            'title' => 'Tinyportal',
            'areas' => array(),
        );
               // Profile area for 2.1
        if( TP_ELK21 ) {
            $profile_areas['tp']['areas']['tpsummary'] = array(
                'label' => $txt['tpsummary'],
                'file' => 'TPSubs.php',
                'function' => 'tp_summary',
                'icon' => 'menu_tp',
                'permission' => array(
                    'own' => 'profile_view_own',
                    'any' => 'profile_view_any',
                ),
            );

            if (!$context['TPortal']['use_wysiwyg']=='0') {
                $profile_areas['tp']['areas']['tparticles'] = array(
                    'label' => $txt['articlesprofile'],
                    'file' => 'TPSubs.php',
                    'function' => 'tp_articles',
                    'icon' => 'menu_tparticle',
                    'permission' => array(
                        'own' => 'profile_view_own',
                        'any' => 'profile_view_any',
                    ),
                    'subsections' => array(
                        'articles' => array($txt['tp-articles'], array('profile_view_own', 'profile_view_any')),
                        'settings' => array($txt['tp-settings'], array('profile_view_own', 'profile_view_any')),
                    ),
                );
            }
            else {
                $profile_areas['tp']['areas']['tparticles'] = array(
                    'label' => $txt['articlesprofile'],
                    'file' => 'TPSubs.php',
                    'function' => 'tp_articles',
                    'icon' => 'menu_tparticle',
                    'permission' => array(
                        'own' => 'profile_view_own',
                        'any' => 'profile_view_any',
                    ),
                );
            }

            if(!empty($context['TPortal']['show_download'])) {
                $profile_areas['tp']['areas']['tpdownload'] = array(
                    'label' => $txt['downloadsprofile'],
                    'file' => 'TPSubs.php',
                    'function' => 'tp_download',
                    'icon' => 'menu_tpdownload',
                    'permission' => array(
                        'own' => 'profile_view_own' && !empty($context['TPortal']['show_download']),
                        'any' => 'profile_view_any' && !empty($context['TPortal']['show_download']),
                    ),
                );
            }

            if(!$context['TPortal']['profile_shouts_hide']) {
                $profile_areas['tp']['areas']['tpshoutbox'] = array(
                    'label' => $txt['shoutboxprofile'],
                    'file' => 'TPShout.php',
                    'function' => 'tp_shoutb',
                    'icon' => 'menu_tpshout',
                    'permission' => array(
                        'own' => 'profile_view_own',
                        'any' => 'profile_view_any',
                    ),
                );
            }
        }
        else {
            // Profile area for 2.0 - no icons 
            $profile_areas['tp']['areas']['tpsummary'] = array(
                'label' => $txt['tpsummary'],
                'file' => 'TPSubs.php',
                'function' => 'tp_summary',
                'permission' => array(
                    'own' => 'profile_view_own',
                    'any' => 'profile_view_any',
                ),
            );

            if (!$context['TPortal']['use_wysiwyg']=='0') {
                $profile_areas['tp']['areas']['tparticles'] = array(
                    'label' => $txt['articlesprofile'],
                    'file' => 'TPSubs.php',
                    'function' => 'tp_articles',
                    'permission' => array(
                        'own' => 'profile_view_own',
                        'any' => 'profile_view_any',
                    ),
                    'subsections' => array(
                        'articles' => array($txt['tp-articles'], array('profile_view_own', 'profile_view_any')),
                        'settings' => array($txt['tp-settings'], array('profile_view_own', 'profile_view_any')),
                    ),
                );
            }
            else {
                $profile_areas['tp']['areas']['tparticles'] = array(
                    'label' => $txt['articlesprofile'],
                    'file' => 'TPSubs.php',
                    'function' => 'tp_articles',
                    'permission' => array(
                        'own' => 'profile_view_own',
                        'any' => 'profile_view_any',
                    ),
                );
            }

            if(!empty($context['TPortal']['show_download'])) {
                $profile_areas['tp']['areas']['tpdownload'] = array(
                    'label' => $txt['downloadsprofile'],
                    'file' => 'TPSubs.php',
                    'function' => 'tp_download',
                    'permission' => array(
                        'own' => 'profile_view_own',
                        'any' => 'profile_view_any',
                    ),
                );
            }

            if(!$context['TPortal']['profile_shouts_hide']) {
                $profile_areas['tp']['areas']['tpshoutbox'] = array(
                    'label' => $txt['shoutboxprofile'],
                    'file' => 'TPShout.php',
                    'function' => 'tp_shoutb',
                    'permission' => array(
                        'own' => 'profile_view_own',
                        'any' => 'profile_view_any',
                    ),
                );
            }

        }

    }}}

    public static function hookActions(&$actionArray) {{{
        $actionArray = array_merge(
            array (
                'tpadmin'   => array('TPortalAdmin.php',    'TPortalAdmin'),
                'forum'     => array('BoardIndex.php',      'BoardIndex'),
                'tportal'   => array('TPortal.php',         'TPortal'),
            ),
            $actionArray
        );
    }}}

    public static function hookDefaultAction() {{{
        global $topic, $board, $context;

        $theAction = false;
        // first..if the action is set, but empty, don't go any further
        if (isset($_REQUEST['action']) && $_REQUEST['action']=='') {
            require_once(SOURCEDIR . '/BoardIndex.php');
            $theAction = 'BoardIndex';
        }

        // Action and board are both empty... maybe the portal page?
        if (empty($board) && empty($topic) && $context['TPortal']['front_type'] != 'boardindex') {
            require_once(SOURCEDIR . '/TPortal.php');
            $theAction = 'TPortalMain';
        }

        // If frontpage set to boardindex but it's an article or category
        if (empty($board) && empty($topic) && $context['TPortal']['front_type'] == 'boardindex' && (isset($_GET['cat']) || isset($_GET['page']))) {
            require_once(SOURCEDIR . '/TPortal.php');
            $theAction = 'TPortalMain';
        }
        // Action and board are still both empty...and no portal startpage - BoardIndex!
        elseif (empty($board) && empty($topic) && $context['TPortal']['front_type'] == 'boardindex') {
            require_once(SOURCEDIR . '/BoardIndex.php');
            $theAction = 'BoardIndex';
        }

        // ELK 2.1 has a default action hook so less source edits
        if(!TP_ELK21) {
            return $theAction;
        }
        else {
            // We need to manually call the action as this function was called be default
            call_user_func($theAction);
        }

    }}}

    public static function hookWhosOnline($actions) {{{
        global $txt, $scripturl;

        loadLanguage('TPortal');

        $dB = Database::getInstance();

        if(isset($actions['page'])) {
            if(is_numeric($actions['page'])) {
                $request = $dB->db_query('', '
                    SELECT subject FROM {db_prefix}tp_articles
                    WHERE id = {int:id}
                    LIMIT 1',
                    array (
                        'id' => $actions['page'],
                    )
                );
            }
            else {
                $request = $dB->db_query('', '
                    SELECT subject FROM {db_prefix}tp_articles
                    WHERE shortname = {string:shortname}
                    LIMIT 1',
                    array (
                        'shortname' => $actions['page'],
                    )
                );
            }
            $article = array();
            if($dB->db_num_rows($request) > 0) {
                while($row = $dB->db_fetch_assoc($request)) {
                    $article = $row;
                }
                $dB->db_free_result($request);
            }
            if(!empty($article)) {
                return sprintf($txt['tp-who-article'], $article['subject'], $actions['page'], $scripturl );
            }
            else {
                return $txt['tp-who-articles'];
            }
        }
        if(isset($actions['cat'])) {
            if(is_numeric($actions['cat'])) {
                $request = $dB->db_query('', '
                    SELECT 	value1 FROM {db_prefix}tp_variables
                    WHERE id = {int:id}
                    LIMIT 1',
                    array (
                        'id' => $actions['cat'],
                    )
                );
            }
            else {
                $request = $dB->db_query('', '
                    SELECT value1 FROM {db_prefix}tp_variables
                    WHERE value8 = {string:shortname}
                    LIMIT 1',
                    array (
                        'shortname' => $actions['cat'],
                    )
                );
            }
            $category = array();
            if($dB->db_num_rows($request) > 0) {
                while($row = $dB->db_fetch_assoc($request)) {
                    $category = $row;
                }
                $dB->db_free_result($request);
            }
            if(!empty($category)) {
                return sprintf($txt['tp-who-category'], $category['value1'], $actions['cat'], $scripturl );
            }
            else {
                return $txt['tp-who-categories'];
            }
        }
        
        if(isset($actions['action']) && $actions['action'] == 'tportal' && isset($actions['dl'])) {
            return $txt['tp-who-downloads'];
        }

        if(isset($actions['action']) && $actions['action'] == 'tportal' && isset($actions['sa']) && ( $actions['sa'] == 'searcharticle' || $actions['sa'] == 'searcharticle2' )) {
            return $txt['tp-who-article-search'];
        }

        if(isset($actions['action']) && $actions['action'] == 'forum') {
            return $txt['tp-who-forum-index'];
        }

    }}}

    public static function hookPreLogStats(&$no_stat_actions) {{{
        $no_stat_actions = array_merge($no_stat_actions, array('shout'));

        // We can also call init from here although it's not meant for this
        require_once(SOURCEDIR . '/TPortal.php');
        \TPortal_init();
    }}}

    public static function hookRedirect(&$setLocation, &$refresh) {{{
        global $scripturl, $context;

        if ($setLocation == $scripturl && !empty($context['TPortal']['redirectforum'])) {
            $setLocation .= '?action=forum';
        }

    }}}

    public static function hookSearchLayers() {{{
        global $context;

        // are we on search page? then add TP search options as well!
        if($context['TPortal']['action'] == 'search') {
            $context['template_layers'][] = 'TPsearch';
        }

    }}}

    public static function hookDisplayButton(&$normal_buttons) {{{
        global $context, $scripturl;

        if(allowedTo(array('tp_settings')) && (($context['TPortal']['front_type']=='forum_selected' || $context['TPortal']['front_type']=='forum_selected_articles'))) {
            if(!in_array($context['current_topic'], explode(',', $context['TPortal']['frontpage_topics']))) {
                $normal_buttons['publish'] = array('active' => true, 'text' => 'tp-publish', 'lang' => true, 'url' => $scripturl . '?action=tportal;sa=publish;t=' . $context['current_topic']);
            }
            else {
                $normal_buttons['unpublish'] = array('active' => true, 'text' => 'tp-unpublish', 'lang' => true, 'url' => $scripturl . '?action=tportal;sa=publish;t=' . $context['current_topic']);
            }
        }
    }}}

    public static function hookLoadTheme(&$id_theme) {{{
        global $modSettings;

        require_once(SOURCEDIR . '/TPSubs.php');

        $theme  = 0;
        $dB     = Database::getInstance();

        // are we on a article? check it for custom theme
        if(isset($_GET['page']) && !isset($_GET['action'])) {
            if (($theme = cache_get_data('tpArticleTheme', 120)) == null) {
                // fetch the custom theme if any
                $pag = Util::filter('page', 'get', 'string');
                if (is_numeric($pag)) {
                    $request = $dB->db_query('', '
                        SELECT id_theme FROM {db_prefix}tp_articles
                        WHERE id = {int:page}',
                        array('page' => (int) $pag)
                    );
                }
                else {
                    $request = $dB->db_query('', '
                        SELECT id_theme FROM {db_prefix}tp_articles
                        WHERE shortname = {string:short}',
                        array('short' => $pag)
                    );
                }
                if($dB->db_num_rows($request) > 0) {
                    $theme = $dB->db_fetch_row($request)[0];
                    $dB->db_free_result($request);
                }

                if (!empty($modSettings['cache_enable'])) {
                    cache_put_data('tpArticleTheme', $theme, 120);
                }
            }
        }
        // are we on frontpage? and it shows fetured article?
        else if(!isset($_GET['page']) && !isset($_GET['action']) && !isset($_GET['board']) && !isset($_GET['topic'])) {
            if (($theme = cache_get_data('tpFrontTheme', 120)) == null) {
                // fetch the custom theme if any
                $request = $dB->db_query('', '
                        SELECT COUNT(*) FROM {db_prefix}tp_settings
                        WHERE name = {string:name}
                        AND value = {string:value}',
                        array('name' => 'front_type', 'value' => 'single_page')
                    );
                if($dB->db_num_rows($request) > 0) {
                    $dB->db_free_result($request);
                    $request = $dB->db_query('', '
                        SELECT art.id_theme
                        FROM {db_prefix}tp_articles AS art
                        WHERE featured = 1' 
                    );
                    if($dB->db_num_rows($request) > 0) {
                        $theme = $dB->db_fetch_row($request)[0];
                        $dB->db_free_result($request);
                    }
                }
                if (!empty($modSettings['cache_enable'])) {
                    cache_put_data('tpFrontTheme', $theme, 120);
                }
            }
        }
        // how about dlmanager, any custom theme there?
        else if(isset($_GET['action']) && $_GET['action'] == 'tportal' && isset($_GET['dl'])) {
            if (($theme = cache_get_data('tpDLTheme', 120)) == null) {
                // fetch the custom theme if any
                $request = $dB->db_query('', '
                    SELECT value FROM {db_prefix}tp_settings
                    WHERE name = {string:name}',
                    array('name' => 'dlmanager_theme')
                );
                if($dB->db_num_rows($request) > 0) {
                    $theme = $dB->db_fetch_row($request)[0];
                    $dB->db_free_result($request);
                }
                if (!empty($modSettings['cache_enable'])) {
                    cache_put_data('tpDLTheme', $theme, 120);
                }
            }
        }

        if($theme != $id_theme && $theme > 0) {
            $id_theme = $theme;
        }

        return $id_theme;
    }}}

}

?>
