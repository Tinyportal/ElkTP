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

use \TinyPortal\Model\Admin as TPAdmin;
use \TinyPortal\Model\Article as TPArticle;
use \TinyPortal\Model\Block as TPBlock;
use \TinyPortal\Model\Database as TPDatabase;
use \TinyPortal\Model\Integrate as TPIntegrate;
use \TinyPortal\Model\Mentions as TPMentions;
use \TinyPortal\Model\Menu as TPMenu;
use \TinyPortal\Model\Permissions as TPPermissions;
use \TinyPortal\Model\Subs as TPSubs;
use \TinyPortal\Model\Util as TPUtil;
use \ElkArte\Errors\Errors;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class MenuAdmin extends BaseAdmin
{

    function __construct() {{{

        parent::__construct();

        loadTemplate('TPMenu');

    }}}

    public function action_index() {{{

		TPSubs::getInstance()->loadLanguage('TPMenu');

        $action = TPUtil::filter('area', 'get', 'string');
        if($action == 'tpmenu') {
		    \isAllowedTo('tp_menu');
            require_once(SUBSDIR . '/Action.class.php');
            $subAction  = TPUtil::filter('sa', 'get', 'string');

            $subActions = array(
                'add'       => array($this, 'action_new', array()),
                'delete'    => array($this, 'action_delete', array()),
                'edit'      => array($this, 'action_edit', array()),
                'list'      => array($this, 'action_list', array()),
                'save'      => array($this, 'action_save', array()),
            );

            $this->context['TPortal']['subaction'] = $subAction;

            TPAdmin::getInstance()->topMenu($subAction);
            TPAdmin::getInstance()->sideMenu($subAction);

            $action     = new \Action();
            $subAction  = $action->initialize($subActions, $subAction);
            $action->dispatch($subAction);
       }

    }}}

    public function action_delete() {{{

        if($id = TPUtil::filter('id', 'get', 'int')) {
            TPMenu::getInstance()->delete($id);
        }

        self::action_list();
    }}}

    public function action_edit() {{{


        $this->context['sub_template'] = 'edit_menu';
    }}}

    public function action_list() {{{

        parent::make_list('menu');

        $this->context['sub_template'] = 'list_menu';
    }}}

    public function action_new() {{{

        $this->context['TPortal']['addmenu']            = array();
        $this->context['TPortal']['addmenu']['types']   = array( 'menu', 'category', 'article', 'link', 'header', 'spacer');
        $this->context['TPortal']['addmenu']['type']    = 'menu';

        $this->context['sub_template'] = 'new_menu';
    }}}

    public function action_save() {{{
        \checksession('post');
        
        // Configure the default array
        $this->context['TPortal']['editmenu'] = array();
        
        $action = TPUtil::filter('tpadmin_form', 'post', 'string');
        switch($action) {
            case 'add':
                if($type = TPUtil::filter('tp_menu_type', 'post', 'string')) {
                    $id = TPMenu::getInstance()->insert(array('type' => $type));
                }
                break;
            case 'edit':
        
                break;
            default:

                break;
        }

        $menu = TPMenu::getInstance()->select(array('id', 'name' ,'type', 'link', 'parent', 'permissions', 'enabled'), array('id' => $id));
        $this->context['TPortal']['editmenu']   = $menu;
        $this->context['sub_template']          = 'edit_menu';

    }}}

    public function list_menu($start, $items_per_page, $sort) {{{
        return TPMenu::getInstance()->list($start, $items_per_page, $sort);
	}}}

	public function list_total_menu() {{{
        return TPMenu::getInstance()->total();
	}}}

}
