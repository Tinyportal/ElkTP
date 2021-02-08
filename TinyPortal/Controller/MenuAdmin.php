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
            require_once(SUBSDIR . '/Action.class.php');
            $subAction  = TPUtil::filter('sa', 'get', 'string');

            $subActions = array(
                'delete'    => array($this, 'action_delete', array()),
                'edit'      => array($this, 'action_edit', array()),
                'list'      => array($this, 'action_list', array()),
                'new'       => array($this, 'action_new', array()),
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


        $this->context['sub_template'] = 'new_menu';
    }}}

    public function list_menu($start, $items_per_page, $sort) {{{
        return TPMenu::getInstance()->list($start, $items_per_page, $sort);
	}}}

	public function list_total_menu() {{{
        return TPMenu::getInstance()->total();
	}}}

}
