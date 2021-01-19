<?php
/**
 * @package TinyPortal
 * @version 1.0.0 RC1
 * @author TinyPortal - http://www.tinyportal.net
 * @license BSD 3.0 http://opensource.org/licenses/BSD-3-Clause/
 *
 * Copyright (C) 2020 - The TinyPortal Team
 *
 */
namespace TinyPortal\Controller;

use \TinyPortal\Model\Admin as TPAdmin;
use \TinyPortal\Model\Database as TPDatabase;
use \TinyPortal\Model\Util as TPUtil;
use ElkArte\Errors\Errors;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class PortalAdmin extends \Action_Controller
{

    public function action_index() {{{
        global $context, $txt;

        return $this->action_admin();

    }}}

    public function action_admin() {{{
		global $context, $txt;

		$area = TPUtil::filter('area', 'get', 'string');
		if($area == 'tpsettings') {

		    \isAllowedTo('tp_settings');

			require_once(SUBSDIR . '/Action.class.php');

			$sa = TPUtil::filter('sa', 'get', 'string');
			if($sa == false) {
				$sa = 'settings';
			}

			$subActions = array (
				'settings'			=> array($this, 'action_settings', array()),
				'updatesettings'	=> array($this, 'update_settings', array()),
				'frontpage'			=> array($this, 'action_frontpage', array()),
				'updatefrontpage'	=> array($this, 'update_frontpage', array()),
			);

			if(\loadLanguage('TPortalAdmin') == false) {
				\loadLanguage('TPortalAdmin', 'english');
			}
			if(\loadLanguage('TPortal') == false) {
				\loadLanguage('TPortal', 'english');
			}
			if(\loadLanguage('TPmodules') == false) {
				\loadLanguage('TPmodules', 'english');
			}

            require_once(SUBSDIR . '/TPortal.subs.php');
			$context['TPortal']['subaction'] = $sa;

			$action     = new \Action();
			$subAction  = $action->initialize($subActions, $sa);
			$action->dispatch($subAction);

			TPAdmin::getInstance()->topMenu($sa);
			TPAdmin::getInstance()->sideMenu($sa);

			$context['sub_template']         = $context['TPortal']['subaction'];

			\loadTemplate('TPortalAdmin');
			\loadTemplate('TPsubs');
		}
		else {
			// Wrap around the old TinyPortal logic for now
            $tpArticleAdmin = new ArticleAdmin();
            $tpArticleAdmin->TPortalAdmin();
		}

    }}}

    public function action_settings() {{{
		global $context;

		$db = TPDatabase::getInstance();

		$context['TPallthem'] = array();
		$request = $db->query('', '
				SELECT th.value AS name, th.id_theme as id_theme, tb.value AS path
				FROM {db_prefix}themes AS th
				LEFT JOIN {db_prefix}themes AS tb ON th.id_theme = tb.id_theme
				WHERE th.variable = {string:thvar}
				AND tb.variable = {string:tbvar}
				AND th.id_member = {int:id_member}
				ORDER BY th.value ASC',
				array(
					'thvar' => 'name', 'tbvar' => 'images_url', 'id_member' => 0,
					)
				);
		if($db->num_rows($request) > 0) {
			while ($row = $db->fetch_assoc($request)) {
				$context['TPallthem'][] = array(
					'id' => $row['id_theme'],
					'path' => $row['path'],
					'name' => $row['name']
				);
			}
			$db->free_result($request);
		}

    }}}

	public function update_settings() {{{
        global $context, $txt;

        $updateArray = array();

        $checkboxes = array('imageproxycheck', 'admin_showblocks', 'oldsidebar', 'disable_template_eval', 'fulltextsearch', 'hideadminmenu', 'useroundframepanels', 'showcollapse', 'blocks_edithide', 'uselangoption', 'use_groupcolor', 'showstars');
        foreach($checkboxes as $v) {
            if(TPUtil::checkboxChecked('tp_'.$v)) {
                $updateArray[$v] = "1";
            }
            else {
                $updateArray[$v] = "";
            }
            // remove the variable so we don't process it twice before the old logic is removed
            unset($_POST['tp_'.$v]);
        }

        foreach($_POST as $what => $value) {
            if(substr($what, 0, 3) == 'tp_') {
                $where = substr($what, 3);
                $clean = $value;
                if($what == 'tp_frontpage_title') {
                    $updateArray['frontpage_title'] = $clean;
                }
                else if(isset($clean)) {
                    $updateArray[$where] = $clean;
                }
                // START non responsive themes form
                if(substr($what, 0, 7) == 'tp_resp') {
                    $postname = substr($what, 7);
                    if(!isset($themeschecked)) {
                        $themeschecked = array();
                    }
                    $themeschecked[] = $postname;
                    if(isset($themeschecked)) {
                        $id = TPAdmin::getInstance()->getSettingData('id', array( 'name' => 'resp' ));
                        TPAdmin::getInstance()->updateSettingData($id[0]['id'], array ('value' => implode(',', $themeschecked)));
                    }
                }
                // END  non responsive themes form
                if($what == 'tp_image_upload_path') {
                    unset($updateArray['image_upload_path']);
                    if(strcmp($context['TPortal']['image_upload_path'],$value) != 0) {
                        // Only allow if part of the boarddir
                        if(strncmp($value, BOARDDIR, strlen(BOARDDIR)) == 0) {
                            // It cann't be part of the existing path
                            if(strncmp($value, $context['TPortal']['image_upload_path'], strlen($context['TPortal']['image_upload_path'])) != 0) {
                                if(\tp_create_dir($value)) {
                                    \tp_recursive_copy($context['TPortal']['image_upload_path'], $value);
                                    \tp_delete_dir($context['TPortal']['image_upload_path']);
                                    $updateArray['image_upload_path'] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }

        \updateTPSettings($updateArray);
        \redirectExit('action=admin;area=tpsettings;sa=settings');

	}}}

    public function action_frontpage() {{{
        global $context, $txt;

        $context['TPortal']['frontpage_visualopts_admin'] = array(
            'left' => 0,
            'right' => 0,
            'center' => 0,
            'top' => 0,
            'bottom' => 0,
            'lower' => 0,
            'nolayer' => 0,
            'sort' => 'date',
            'sortorder' => 'desc'
        );

        $w = explode(',', $context['TPortal']['frontpage_visual']);

        foreach(array('left', 'right', 'center', 'top', 'bottom', 'lower', 'nolayer') as $type) {
            if(in_array($type, $w)) {
                $context['TPortal']['frontpage_visualopts_admin'][$type] = 1;
            }
        }
        foreach($w as $r) {
            if(substr($r, 0, 5) == 'sort_') {
                $context['TPortal']['frontpage_visualopts_admin']['sort'] = substr($r, 5);
            }
            elseif(substr($r ,0, 10) == 'sortorder_') {
                $context['TPortal']['frontpage_visualopts_admin']['sortorder'] = substr($r, 10);
            }
        }

        $context['TPortal']['SSI_boards'] = explode(',', $context['TPortal']['SSI_board']);

        \get_boards();
        \get_catlayouts();


    }}}

	public function update_frontpage() {{{
        global $context;

        $updateArray    = array();
        $checkboxes     = array('allow_guestnews', 'forumposts_avatar', 'use_attachment');
        foreach($checkboxes as $v) {
            if(TPUtil::checkboxChecked('tp_'.$v)) {
                $updateArray[$v] = "1";
            }
            else {
                $updateArray[$v] = "";
            }
            // remove the variable so we don't process it twice before the old logic is removed
            unset($_POST['tp_'.$v]);
        }

		foreach($_POST as $what => $value) {
			if(substr($what, 0, 3) == 'tp_') {
				$where = substr($what, 3);
				$clean = $value;
                // for frontpage, do some extra
                if(substr($what, 0, 20) == 'tp_frontpage_visual_') {
                    $w[] = substr($what, 20);
                    unset($clean);
                }
                elseif(substr($what, 0, 21) == 'tp_frontpage_usorting') {
                    $w[] = 'sort_'.$value;
                    unset($clean);
                }
                elseif(substr($what, 0, 26) == 'tp_frontpage_sorting_order') {
                    $w[] = 'sortorder_'.$value;
                    unset($clean);
                }
                // SSI boards
                elseif(substr($what, 0, 11) == 'tp_ssiboard') {
                    $data   = file_get_contents("php://input");
                    $output = TPUtil::http_parse_query($data)['tp_ssiboard'];
                    if(is_string($output)) {
                        $ssi[] = $output;
                    }
                    else if(is_array($output)) {
                        $ssi = $output;
                    }
                    else {
                        $ssi = array();
                    }
                }
				if(isset($clean)) {
					$updateArray[$where] = $clean;
				}
			}
		}

		// check the frontpage visual setting..
        if(isset($w)) {
		    $updateArray['frontpage_visual'] = implode(',', $w);
        }
        if(isset($ssi)) {
		    $updateArray['SSI_board'] = implode(',', $ssi);
        }

        \updateTPSettings($updateArray);
        \redirectExit('action=admin;area=tpsettings;sa=frontpage');
	}}}

}

?>
