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
namespace TinyPortal\Model;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Admin extends Base {

    private $dBStructure        = array();
    private $tpSettings         = array();
    private static $_instance   = null;

    public static function getInstance() {{{

    	if(self::$_instance == null) {
			self::$_instance = new self();
		}

    	return self::$_instance;

    }}}

    // Empty Clone method
    private function __clone() { }


    public function __construct() {{{
        parent::__construct();

        $this->dBStructure = array (
            'id'        => 'mediumint',
            'name'      => 'text',
            'value'     => 'text',
        );

        $this->tpSettings = $this->getSetting();

    }}}

    public function getSettingData( $columns, array $where ) {{{

        return self::getSQLData($columns, $where, $this->dBStructure, 'tp_settings');

    }}}

    public function getSetting( string $setting_name = null , bool $refresh = false ) {{{

        if($refresh == false && !is_null($setting_name) && array_key_exists($setting_name, $this->tpSettings)) {
            return $this->tpSettings[$setting_name];
        }

        $settings = array();

        if(empty($setting_name)) {
            $request =  $this->dB->db_query('', '
                SELECT name, value
                FROM {db_prefix}tp_settings
                WHERE 1=1'
            );

            if($this->dB->db_num_rows($request) > 0) {
                while($row = $this->dB->db_fetch_assoc($request)) {
                    $settings[$row['name']] = $row['value'];
                }
            }
        }
        else {
            $request =  $this->dB->db_query('', '
                SELECT value FROM {db_prefix}tp_settings
                WHERE name = {string:setting_name} LIMIT 1',
                array (
                    'setting_name' => $setting_name
                )
            );

            if($this->dB->db_num_rows($request) > 0) {
                $row                        = $this->dB->db_fetch_assoc($request);
                $settings[$setting_name]    = $row['value'];
                return $row['value'];
            }
        }

        $this->dB->db_free_result($request);

        return $settings;

    }}}

   public function insertSetting( array $settings_data) {{{

        return self::insertSQL($settings_data, $this->dBStructure, 'tp_settings');

    }}}

     public function updateSetting( int $settings_id, array $settings_data) {{{

        return self::updateSQL($settings_id, $settings_data, $this->dBStructure, 'tp_settings');

    }}}

    public function deleteSetting( int $settings_id ) {{{

        return self::deleteSQL($settings_id, 'tp_settings');

    }}}

	public function topMenu( $area ) {{{

	global $scripturl, $context, $txt;

	// done with all POST values, go to the correct screen
	$context['TPortal']['subtabs'] = '';
    if(in_array($area, array('articles', 'addarticle_php', 'addarticle_html', 'addarticle_bbc', 'addarticle_import', 'strays', 'submission')) && allowedTo('tp_articles')) {
        $context['TPortal']['subtabs'] = array(
				'articles' => array(
					'lang' => true,
					'text' => 'tp-articles',
					'url' => $scripturl . '?action=admin;area=tparticles;sa=articles',
					'active' => ($context['TPortal']['subaction'] == 'articles' || $context['TPortal']['subaction'] == 'editarticle') && $context['TPortal']['subaction'] != 'strays',
					),
				'articles_nocat' => array(
					'lang' => true,
					'text' => 'tp-uncategorised' ,
					'url' => $scripturl . '?action=admin;area=tparticles;sa=strays',
					'active' => $context['TPortal']['subaction'] == 'strays',
					),
				'submissions' => array(
					'lang' => true,
					'text' => 'tp-tabs4' ,
					'url' => $scripturl . '?action=admin;area=tparticles;sa=submission',
					'active' => $context['TPortal']['subaction'] == 'submission',
					),
				'addarticle' => array(
					'lang' => true,
					'text' => 'tp-tabs2',
					'url' => $scripturl . '?action=admin;area=tparticles;sa=addarticle_html' . (isset($_GET['cu']) ? ';cu='.$_GET['cu'] : ''),
					'active' => $context['TPortal']['subaction'] == 'addarticle_html',
					),
				'addarticle_php' => array(
					'lang' => true,
					'text' => 'tp-tabs3',
					'url' => $scripturl . '?action=admin;area=tparticles;sa=addarticle_php' . (isset($_GET['cu']) ? ';cu='.$_GET['cu'] : ''),
					'active' => $context['TPortal']['subaction'] == 'addarticle_php',
					),
				'addarticle_bbc' => array(
					'lang' => true,
					'text' => 'tp-addbbc',
					'url' => $scripturl . '?action=admin;area=tparticles;sa=addarticle_bbc' . (isset($_GET['cu']) ? ';cu='.$_GET['cu'] : ''),
					'active' => $context['TPortal']['subaction'] == 'addarticle_bbc',
					),
				'article_import' => array(
					'lang' => true,
					'text' => 'tp-addimport',
					'url' => $scripturl . '?action=admin;area=tparticles;sa=addarticle_import' . (isset($_GET['cu']) ? ';cu='.$_GET['cu'] : ''),
					'active' => $context['TPortal']['subaction'] == 'addarticle_import',
					),
				);
    }
    elseif(in_array($area, array('newcategory','categories','clist')) && allowedTo('tp_articles')) {
        $context['TPortal']['subtabs'] = array(
                'categories' => array(
                    'lang' => true,
                    'text' => 'tp-tabs5',
                    'url' => $scripturl . '?action=admin;area=tparticles;sa=categories',
                    'active' => $area == 'categories',
                    ),
                'newcategory' => array(
                    'lang' => true,
                    'text' => 'tp-tabs6',
                    'url' => $scripturl . '?action=admin;area=tparticles;sa=newcategory',
                    'active' => $area == 'newcategory',
                    ),
                'clist' => array(
                    'lang' => true,
                    'text' => 'tp-tabs11',
                    'url' => $scripturl . '?action=admin;area=tparticles;sa=clist',
                    'active' => $area == 'clist',
                    ),
                );
    }
    elseif(in_array($area, array('blocks','panels')) && allowedTo('tp_blocks')) {
        $context['TPortal']['subtabs'] = array(
                'panels' => array(
                    'lang' => true,
                    'text' => 'tp-panels',
                    'url' => $scripturl . '?action=admin;area=tpblocks;sa=panels',
                    'active' => $area == 'panels',
                ),
				'blocks' => array(
                    'lang' => true,
                    'text' => 'tp-blocks',
                    'url' => $scripturl . '?action=admin;area=tpblocks;sa=blocks',
                    'active' => $area == 'blocks',
                ),
				'addblock' => array(
                    'lang' => true,
                    'text' => 'tp-addblock',
                    'url' => $scripturl . '?action=admin;area=tpblocks;sa=addblock;' . $context['session_var'] . '=' . $context['session_id'].'',
                    'active' => $area == 'addblock',
                ),
                'blockoverview' => array(
                    'lang' => true,
                    'text' => 'tp-blockoverview',
                    'url' => $scripturl . '?action=admin;area=tpblocks;sa=blockoverview',
                    'active' => $area == 'blockoverview',
                ),
            );
    }

    if(!in_array('tpadm', \Template_Layers::getInstance()->getLayers())) {
        \Template_Layers::getInstance()->add('tpadm');
        \Template_Layers::getInstance()->add('subtab');
    }

}}}

// Set up the administration sections.
	public function sideMenu($area = '') {{{
		global $txt, $context, $scripturl;

		if(Subs::getInstance()->loadLanguage('TPortalAdmin') == false) {
			Subs::getInstance()->loadLanguage('TPortalAdmin', 'english');
		}

		$context['admin_tabs'] = array();
		$context['admin_header']['tp_settings'] = $txt['tp-adminheader1'];
		$context['admin_header']['tp_articles'] = $txt['tp-articles'];
		$context['admin_header']['tp_blocks']   = $txt['tp-adminpanels'];
		$context['admin_header']['tp_menu']     = $txt['tp-adminmenus'];
		$context['admin_header']['tp_download'] = $txt['tp-admindownload'];
		$context['admin_header']['tp_gallery']  = $txt['tp-admingallery'];

		if (allowedTo('tp_settings')) {
			$context['admin_tabs']['tp_settings'] = array(
				'settings' => array(
					'title' => $txt['tp-settings'],
					'description' => $txt['tp-settingdesc1'],
					'href' => $scripturl . '?action=admin;area=tpsettings;sa=settings',
					'is_selected' => $area == 'settings',
				),
				'frontpage' => array(
					'title' => $txt['tp-frontpage'],
					'description' => $txt['tp-frontpagedesc1'],
					'href' => $scripturl . '?action=admin;area=tpsettings;sa=frontpage',
					'is_selected' => $area == 'frontpage',
				),
			);
		}

		if (allowedTo('tp_articles')) {
			$context['admin_tabs']['tp_articles'] = array(
				'articles' => array(
					'title' => $txt['tp-articles'],
					'description' => $txt['tp-articledesc1'],
					'href' => $scripturl . '?action=admin;area=tparticles;sa=articles',
					'is_selected' => (substr($area,0,11)=='editarticle' || in_array($area, array('articles','addarticle','addarticle_php', 'addarticle_bbc', 'addarticle_import','strays','submission'))),
				),
				'categories' => array(
					'title' => $txt['tp-tabs5'],
					'description' => $txt['tp-articledesc2'],
					'href' => $scripturl . '?action=admin;area=tparticles;sa=categories',
					'is_selected' => in_array($area, array('categories', 'newcategory','clist')) ,
				),
				'artsettings' => array(
					'title' => $txt['tp-settings'],
					'description' => $txt['tp-articledesc3'],
					'href' => $scripturl . '?action=admin;area=tparticles;sa=artsettings',
					'is_selected' => $area == 'artsettings',
				),
				'icons' => array(
					'title' => $txt['tp-adminicons'],
					'description' => $txt['tp-articledesc5'],
					'href' => $scripturl . '?action=admin;area=tparticles;sa=articons',
					'is_selected' => $area == 'articons',
				),
			);
		}

		if (allowedTo('tp_blocks')) {
			$context['admin_tabs']['tp_blocks'] = array(
				'panelsettings' => array(
					'title' => $txt['tp-allpanels'],
					'description' => $txt['tp-paneldesc1'],
					'href' => $scripturl . '?action=admin;area=tpblocks;sa=panels',
					'is_selected' => $area == 'panels',
				),
				'blocks' => array(
					'title' => $txt['tp-allblocks'],
					'description' => $txt['tp-blocksdesc1'],
					'href' => $scripturl . '?action=admin;area=tpblocks;sa=blocks',
					'is_selected' => $area == 'blocks' && !isset($_GET['latest']) && !isset($_GET['overview']),
				),
				'blockoverview' => array(
					'title' => $txt['tp-blockoverview'],
					'description' => '',
					'href' => $scripturl . '?action=admin;area=tpblocks;sa=blockoverview',
					'is_selected' => ($area == 'blocks' && isset($_GET['overview'])) || substr($area,0,9) == 'editblock',
				),
			);
		}

		if ( allowedTo('tp_menu') && ($this->getSetting('menu_enabled') == true) ) {
			$context['admin_tabs']['tp_menu'] = array(
				'list' => array(
					'title' => $txt['tp-menu-list'],
					'href' => $scripturl . '?action=admin;area=tpmenu;sa=list',
					'is_selected' => $area == 'list' && (Util::filter('area', 'get', 'string') == 'tpmenu'),
				),
				'add' => array(
					'title' => $txt['tp-menu-add'],
					'href' => $scripturl . '?action=admin;area=tpmenu;sa=add',
					'is_selected' => $area == 'add' && (Util::filter('area', 'get', 'string') == 'tpmenu'),
				),
			);
		}

		if ( allowedTo('tp_download') && ($this->getSetting('download_enabled') == true) ) {
			$context['admin_tabs']['tp_download'] = array(
				'list' => array(
					'title' => $txt['tp-download-list'],
					'href' => $scripturl . '?action=admin;area=tpdownload;sa=list',
					'is_selected' => $area == 'list' && (Util::filter('area', 'get', 'string') == 'tpdownload'),
				),
				'add' => array(
					'title' => $txt['tp-download-add'],
					'href' => $scripturl . '?action=admin;area=tpdownload;sa=add',
					'is_selected' => $area == 'add' && (Util::filter('area', 'get', 'string') == 'tpdownload'),
				),
			);
		}

		if ( allowedTo('tp_gallery') && ($this->getSetting('gallery_enabled') == true) ) {
			$context['admin_tabs']['tp_gallery'] = array(
				'list' => array(
					'title'         => $txt['tp-gallery-list'],
					'href'          => $scripturl . '?action=admin;area=tpgallery;sa=list',
					'is_selected'   => $area == 'list' && (Util::filter('area', 'get', 'string') == 'tpgallery'),
				),
				'add' => array(
					'title'         => $txt['tp-gallery-add'],
					'href'          => $scripturl . '?action=admin;area=tpgallery;sa=add',
					'is_selected'   => $area == 'add' && (Util::filter('area', 'get', 'string') == 'tpgallery'),
				),
			);
		}

	}}}

}

?>
