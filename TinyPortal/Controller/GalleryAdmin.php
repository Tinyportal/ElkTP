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
use \TinyPortal\Model\Gallery as TPGallery;
use \TinyPortal\Model\Integrate as TPIntegrate;
use \TinyPortal\Model\Mentions as TPMentions;
use \TinyPortal\Model\Permissions as TPPermissions;
use \TinyPortal\Model\Subs as TPSubs;
use \TinyPortal\Model\Util as TPUtil;
use \ElkArte\Errors\Errors;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class GalleryAdmin extends BaseAdmin
{
    public function __construct() {{{
        parent::__construct();

        loadTemplate('TPGallery');
    }}}

    public function action_index() {{{
        global $context, $txt;

		TPSubs::getInstance()->loadLanguage('TPGallery');

        $action = TPUtil::filter('area', 'get', 'string');
        if($action == 'tpgallery') {
            require_once(SUBSDIR . '/Action.class.php');
            $subAction  = TPUtil::filter('sa', 'get', 'string');

            $subActions = array(
                'delete'    => array($this, 'action_delete', array()),
                'edit'      => array($this, 'action_edit', array()),
                'list'      => array($this, 'action_list', array()),
                'new'       => array($this, 'action_new', array()),
            );

            $context['TPortal']['subaction'] = $subAction;

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


    }}}

    public function action_list() {{{
       parent::make_list('gallery');

        $this->context['sub_template'] = 'list_gallery';
    }}}

    public function action_new() {{{


    }}}

    public function list_gallery($start, $items_per_page, $sort) {{{
        return TPGallery::getInstance()->list($start, $items_per_page, $sort);
	}}}

	public function list_total_gallery() {{{
        return TPGallery::getInstance()->total();
	}}}

}

?>
