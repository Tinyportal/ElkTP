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
namespace TinyPortal;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Integrate
{

    public static function hookPreLoad() {{{
	    global $modSettings; // Needed for frontPageHook

        // We need to load our autoloader outside of the main function
        if(!defined('ELK_BACKWARDS_COMPAT')) {
            define('ELK_BACKWARDS_COMPAT', true);
            self::setup_db_backwards_compat();

			$loader = new \ElkArte\ext\Composer\Autoload\ClassLoader();
			$loader->setPsr4('TinyPortal\\', BOARDDIR . '/TinyPortal');
			$loader->register();
        }

        // Should check TinyPortal is enabled..
        if( Model\Admin::getInstance()->getSetting('front_type') != 'boardindex' ) {
            $modSettings['front_page']  = '\TinyPortal\Controller\Portal';
        }

        $hooks = array (
            'current_action'                    => '\TinyPortal\Integrate::hookCurrentAction',
            'load_permissions'                  => '\TinyPortal\Integrate::hookPermissions',
            'load_illegal_guest_permissions'    => '\TinyPortal\Integrate::hookIllegalPermissions',
            'buffer'                            => '\TinyPortal\Integrate::hookBuffer',
            'menu_buttons'                      => '\TinyPortal\Integrate::hookMenuButtons',
            'display_buttons'                   => '\TinyPortal\Integrate::hookDisplayButtons',
            'admin_areas'                       => '\TinyPortal\Integrate::hookAdminAreas',
            'actions'                           => '\TinyPortal\Integrate::hookActions',
            'whos_online'                       => '\TinyPortal\Integrate::hookWhosOnline',
            'profile_areas'                     => '\TinyPortal\Integrate::hookProfileArea',
            'pre_load_theme'                    => '\TinyPortal\Integrate::hookLoadTheme',
            'redirect'                          => '\TinyPortal\Integrate::hookRedirect',
            'action_frontpage'                  => '\TinyPortal\Integrate::hookFrontPage',
            'init_theme'                        => '\TinyPortal\Integrate::hookInitTheme',
            'search'                            => '\TinyPortal\Integrate::hookSearchLayers',
            'tp_pre_subactions'                 => array (
            ),
            'tp_post_subactions'                => array (
            ),
            'tp_post_init'                      => array (
                'BOARDDIR/TinyPortal/Controller/Block.php|\TinyPortal\Controller\Block::loadBlocks',
            ),
            'tp_admin_areas'                    => array (
            ),
            'tp_shoutbox'                       => array (
            ),
            'tp_block'                          => array (
            ),
            'tp_pre_admin_subactions'           => array (
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
        if( Model\Admin::getInstance()->getSetting('front_type') != 'boardindex' ) {
            $modSettings['front_page']  = '\TinyPortal\Controller\Portal';
        }

	    return;
    }}}

    public static function setup_db_backwards_compat() {{{
        global $db_type, $context;

        if($db_type == 'postgresql') {
            define('TP_PGSQL', true);
        }
        else {
            define('TP_PGSQL', false);
        }

        define('TP_VERSION',        '1.0.0 RC3');
        define('TP_SHORT_VERSION',  '100RC3');

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
                'tp_settings'       => array(false, 'tp', 'tp'),
                'tp_blocks'         => array(false, 'tp', 'tp'),
                'tp_menu'           => array(false, 'tp', 'tp'),
                'tp_download'       => array(false, 'tp', 'tp'),
                'tp_gallery'        => array(false, 'tp', 'tp'),
                'tp_articles'       => array(false, 'tp', 'tp'),
                'tp_submithtml'     => array(false, 'tp', 'tp'),
                'tp_submitbbc'      => array(false, 'tp', 'tp'),
                'tp_editownarticle' => array(false, 'tp', 'tp'),
				'tp_alwaysapproved' => array(false, 'tp', 'tp'),
                'tp_artcomment'     => array(false, 'tp', 'tp'),
            ),
            $permissionList['membergroup']
        );

    }}}

    // Adds TP copyright in the buffer so we don't have to edit an ELK file
    public static function hookBuffer($buffer) {{{
        global $context, $scripturl, $txt, $boardurl;

        // add upshrink buttons
        if( array_key_exists('TPortal', $context) && !empty($context['TPortal']['upshrinkpanel']) ) {
            $buffer = preg_replace('~<ul class="navigate_section">~', '<ul class="navigate_section"><li class="tp_upshrink21">'.$context['TPortal']['upshrinkpanel'].'</li>', $buffer, 1);
        }

        // Dynamic body ID
        if (isset($context['TPortal']) && $context['TPortal']['action'] == 'profile') {
            $bodyid = "profilepage";
        } elseif (isset($context['TPortal']) && $context['TPortal']['action'] == 'pm') {
            $bodyid = "pmpage";
        } elseif (isset($context['TPortal']) && $context['TPortal']['action'] == 'calendar') {
            $bodyid = "calendarpage";
        } elseif (isset($context['TPortal']) && $context['TPortal']['action'] == 'memberlist') {
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


        $string = '<a target="_blank" href="https://www.tinyportal.net" title="TinyPortal">TinyPortal '.TP_VERSION.'</a> &copy; <a href="' . $scripturl . '?action=tportal;sa=credits" title="Credits">2005-2021</a>';

        if (ELK == 'SSI' || strpos($buffer, $string) !== false) {
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
            $find       = array( sprintf('%1$s</a>', FORUM_VERSION), );
            $replace    = array( sprintf('%1$s</a> | ', FORUM_VERSION) . $string , );
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
            'tp_menu',
            'tp_download',
            'tp_gallery',
            'tp_articles',
            'tp_submithtml',
            'tp_submitbbc',
            'tp_editownarticle',
			'tp_alwaysapproved',
            'tp_artcomment',
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

		Model\Subs::getInstance()->loadLanguage('TPortal');

        if(Model\Admin::getInstance()->getSetting('front_type') != 'boardindex' ) {
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
        }

        if(Model\Admin::getInstance()->getSetting('hideadminmenu') != '1' ) {
            $subButtons = array();
            Model\Admin::getInstance()->sideMenu();
            foreach($context['admin_tabs'] as $k => $v) {
                $subButtons[$k]['show']     = true;
                $subButtons[$k]['href']     = $scripturl.'?action=admin;area='.str_replace('_', '', $k);
                $subButtons[$k]['title']    = $txt[str_replace('_', '-', $k)];
                if(is_array($v)) {
                    foreach($v as $kk => $vv) {
                        $subButtons[$k]['sub_buttons'][$vv['title']]['show']    = true;
                        $subButtons[$k]['sub_buttons'][$vv['title']]['title']   = $vv['title'];
                        $subButtons[$k]['sub_buttons'][$vv['title']]['href']    = $vv['href'];
                    }
                }
            }

            $buttons = \elk_array_insert($buttons, 'unreadreplies', array (
                    'tpadmin' => array(
                        'title' 	    => $txt['tp-tphelp'],
                        'href' 		    => $scripturl.'?action=admin;area=tpsettings',
                        'show'          => true,
                        'action_hook' 	=> true,
                        'sub_buttons'   => $subButtons,
                    ),
                ), 'after'
            );
        }

    }}}

    public static function hookDisplayButtons() {{{

        global $context, $scripturl, $txt;

        if(allowedTo(array('tp_settings')) && (($context['TPortal']['front_type'] == 'forum_selected' || $context['TPortal']['front_type'] == 'forum_selected_articles'))) {
            if(!in_array($context['current_topic'], explode(',', $context['TPortal']['frontpage_topics']))) {
                $context['normal_buttons']['publish'] = array('active' => false, 'text' => 'tp-publish', 'lang' => true, 'url' => $scripturl . '?action=tportal;sa=publish;t=' . $context['current_topic']);
            }
            else {
                $context['normal_buttons']['unpublish'] = array('active' => true, 'text' => 'tp-unpublish', 'lang' => true, 'url' => $scripturl . '?action=tportal;sa=publish;t=' . $context['current_topic']);
            }
        }

    }}}

    public static function hookAdminAreas($adminAreas) {{{
        global $txt;

        Model\Subs::getInstance()->loadLanguage('TPortal');
        Model\Subs::getInstance()->loadLanguage('TPortalAdmin');

        $menu['tpadmin'] = array (
			'title' => $txt['tp-tphelp'],
			'permission' => array ('admin_forum', 'tp_articles', 'tp_blocks', 'tp_settings', 'tp_menu', 'tp_download', 'tp_gallery'),
			'areas' => array (
				'tpsettings' => array (
					'label'       => $txt['tp-adminheader1'],
					'controller'  => '\\TinyPortal\Controller\PortalAdmin',
					'function'    => 'action_index',
					'icon'        => 'transparent.png',
					'permission'  => array ( 'admin_forum', 'tp_settings' ),
					'subsections' => array (
						'settings'	    => array ( $txt['tp-settings'] ),
						'frontpage'	    => array ( $txt['tp-frontpage'] ),
					),
				),
				'tparticles' => array (
					'label'       => $txt['tp-articles'],
					'controller'  => '\\TinyPortal\Controller\ArticleAdmin',
					'function'    => 'action_index',
					'icon'        => 'transparent.png',
					'permission'  => array ( 'admin_forum', 'tp_articles' ),
					'subsections' => array (
						'articles'	    => array ( $txt['tp-articles'] ),
						'categories'    => array ( $txt['tp-tabs5'] ),
					),
				),
				'tpblocks' => array (
					'label'       => $txt['tp-adminpanels'],
					'controller'  => '\\TinyPortal\Controller\BlockAdmin',
					'function'    => 'action_index',
					'icon'        => 'transparent.png',
					'permission'  => array ( 'admin_forum', 'tp_blocks' ),
					'subsections' => array (
						'blocks'	    => array ( $txt['tp-blocks'] ),
						'panels'	    => array ( $txt['tp-panels'] ),
					),
				),
			),
		);
	
		

        if( Model\Admin::getInstance()->getSetting('menu_enabled') == true ) {
			$adminAreas['tpadmin']['areas']['tpmenu'] = array (
				'label'       => $txt['tp-adminmenus'],
				'controller'  => '\\TinyPortal\Controller\MenuAdmin',
				'function'    => 'action_index',
				'icon'        => 'transparent.png',
				'permission'  => array ( 'admin_forum', 'tp_menu' ),
				'subsections' => array (
					'list'	    => array ( $txt['tp-menu-list'] ),
					'add'	    => array ( $txt['tp-menu-add'] ),
				),
			);
		}


        if( Model\Admin::getInstance()->getSetting('download_enabled') == true ) {
			$adminAreas['tpadmin']['areas']['tpdownload'] = array (
				'label'       => $txt['tp-admindownload'],
				'controller'  => '\\TinyPortal\Controller\DownloadAdmin',
				'function'    => 'action_index',
				'icon'        => 'transparent.png',
				'permission'  => array ( 'admin_forum', 'tp_download' ),
				'subsections' => array (
					'list'	    => array ( $txt['tp-download-list'] ),
					'add'	    => array ( $txt['tp-download-add'] ),
				),
			);
		}
		
        if( Model\Admin::getInstance()->getSetting('gallery_enabled') == true ) {
			$adminAreas['tpadmin']['areas']['tpgallery'] = array (
				'label'       => $txt['tp-admingallery'],
				'controller'  => '\\TinyPortal\Controller\GalleryAdmin',
				'function'    => 'action_index',
				'icon'        => 'transparent.png',
				'permission'  => array ( 'admin_forum', 'tp_gallery' ),
				'subsections' => array (
					'list'	    => array ( $txt['tp-gallery-list'] ),
					'add'	    => array ( $txt['tp-gallery-add'] ),
				),
			);
		}

		$adminAreas->addMenuData($menu);

    }}}

    public static function hookProfileArea(&$profile_areas) {{{
        global $txt, $context;

        $profile_areas['tp'] = array(
            'title' => 'Tinyportal',
            'areas' => array(),
        );

        // Profile area for 1.0
        $profile_areas['tp']['areas']['tpsummary'] = array(
            'label'         => $txt['tpsummary'],
            'controller'    => '\\TinyPortal\\Controller\\Profile',
            'function'      => 'summary',
            'icon'          => 'menu_tp',
            'permission'    => array(
                'own'       => 'profile_view_own',
                'any'       => 'profile_view_any',
            ),
        );

        if (!$context['TPortal']['use_wysiwyg']=='0') {
            $profile_areas['tp']['areas']['tparticles'] = array(
                'label'         => $txt['articlesprofile'],
                'controller'    => '\\TinyPortal\\Controller\\Profile',
                'function'      => 'articles',
                'icon'          => 'menu_tparticle',
                'permission'    => array(
                    'own'       => 'profile_view_own',
                    'any'       => 'profile_view_any',
                ),
                'subsections'   => array(
                    'articles'  => array($txt['tp-articles'], array('profile_view_own', 'profile_view_any')),
                    'settings'  => array($txt['tp-settings'], array('profile_view_own', 'profile_view_any')),
                ),
            );
        }
        else {
            $profile_areas['tp']['areas']['tparticles'] = array(
                'label'         => $txt['articlesprofile'],
                'controller'    => '\\TinyPortal\\Controller\\Profile',
                'function'      => 'articles',
                'icon'          => 'menu_tparticle',
                'permission'    => array(
                    'own'       => 'profile_view_own',
                    'any'       => 'profile_view_any',
                ),
            );
        }

    }}}

    public static function hookActions(&$actionArray) {{{

		$actionArray = array_merge(
			$actionArray,
			array (
				'forum'     => array('\TinyPortal\Controller\BoardIndex', 'action_index'),
				'tparticle' => array('\TinyPortal\Controller\Article',  'action_index'),
				'tportal'   => array('\TinyPortal\Controller\Portal',   'action_index'),
				'tpsearch'  => array('\TinyPortal\Controller\Search',   'action_index'),
			)
		);

    }}}

    public static function hookWhosOnline($actions) {{{
        global $txt, $scripturl;

        Model\Subs::getInstance()->loadLanguage('TPortal');

        $dB = Model\Database::getInstance();

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
            }
            $dB->db_free_result($request);

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
                    SELECT 	display_name FROM {db_prefix}tp_categories
                    WHERE id = {int:id}
                    LIMIT 1',
                    array (
                        'id' => $actions['cat'],
                    )
                );
            }
            else {
                $request = $dB->db_query('', '
                    SELECT display_name FROM {db_prefix}tp_categories
                    WHERE short_name = {string:shortname}
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
            }
            $dB->db_free_result($request);

            if(!empty($category)) {
                return sprintf($txt['tp-who-category'], $category['display_name'], $actions['cat'], $scripturl );
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

    public static function hookInitTheme($id_theme, &$settings) {{{

        // Add our custom theme directory
        theme()->getTemplates()->getDirectory()->addDirectory(BOARDDIR . '/TinyPortal/Views/');

        Model\Portal::getInstance()->init();

    }}}

    public static function hookRedirect(&$setLocation) {{{
        global $scripturl, $context;

        if ($setLocation == $scripturl && !empty($context['TPortal']['redirectforum'])) {
            $setLocation .= '?action=forum';
        }

    }}}

    public static function hookSearchLayers() {{{
        global $context;

        // are we on search page? then add TP search options as well!
        if($context['TPortal']['action'] == 'search') {
            if(!in_array('TPsearch', \theme()->getLayers()->getLayers())) {
                \theme()->getLayers()->add('TPSearch');
            }
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

        $theme  = 0;
        $dB     = Model\Database::getInstance();

        // are we on a article? check it for custom theme
        if(isset($_GET['page']) && !isset($_GET['action'])) {
            if (($theme = \ElkArte\Cache\Cache::instance()->get('tpArticleTheme', 120)) == null) {
                // fetch the custom theme if any
                $pag = Model\Util::filter('page', 'get', 'string');
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
                }
                $dB->db_free_result($request);

                if (!empty($modSettings['cache_enable'])) {
                    \ElkArte\Cache\Cache::instance()->put('tpArticleTheme', $theme, 120);
                }
            }
        }
        // are we on frontpage? and it shows fetured article?
        else if(!isset($_GET['page']) && !isset($_GET['action']) && !isset($_GET['board']) && !isset($_GET['topic'])) {
            if (($theme = \ElkArte\Cache\Cache::instance()->get('tpFrontTheme', 120)) == null) {
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
                    }
                    $dB->db_free_result($request);
                }
                if (!empty($modSettings['cache_enable'])) {
                    \ElkArte\Cache\Cache::instance()->put('tpFrontTheme', $theme, 120);
                }
            }
        }
        // how about dlmanager, any custom theme there?
        else if(isset($_GET['action']) && $_GET['action'] == 'tportal' && isset($_GET['dl'])) {
            if (($theme = \ElkArte\Cache\Cache::instance()->get('tpDLTheme', 120)) == null) {
                // fetch the custom theme if any
                $request = $dB->db_query('', '
                    SELECT value FROM {db_prefix}tp_settings
                    WHERE name = {string:name}',
                    array('name' => 'dlmanager_theme')
                );
                if($dB->db_num_rows($request) > 0) {
                    $theme = $dB->db_fetch_row($request)[0];
                }
                $dB->db_free_result($request);
                if (!empty($modSettings['cache_enable'])) {
                    \ElkArte\Cache\Cache::instance()->put('tpDLTheme', $theme, 120);
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
