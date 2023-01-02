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

use \TinyPortal\Model\Admin as TPAdmin;
use \TinyPortal\Model\Article as TPArticle;
use \TinyPortal\Model\Block as TPBlock;
use \TinyPortal\Model\Category as TPCategory;
use \TinyPortal\Model\Database as TPDatabase;
use \TinyPortal\Model\Download as TPDownload;
use \TinyPortal\Model\Integrate as TPIntegrate;
use \TinyPortal\Model\Mentions as TPMentions;
use \TinyPortal\Model\Permissions as TPPermissions;
use \TinyPortal\Model\Subs as TPSubs;
use \TinyPortal\Model\Util as TPUtil;
use \ElkArte\Errors\Errors;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class DownloadAdmin extends BaseAdmin
{
    public function __construct() {{{
		parent::__construct(new \ElkArte\EventManager());
        theme()->getTemplates()->load('TPDownloadAdmin');
    }}}

    public function action_index() {{{
        global $context, $txt;

		TPSubs::getInstance()->loadLanguage('TPDownload');

        $action = TPUtil::filter('area', 'get', 'string');
        if($action == 'tpdownload') {
            $subAction  = TPUtil::filter('sa', 'get', 'string');

            $subActions = array(
                'delete'    => array($this, 'action_delete', array()),
                'edit'      => array($this, 'action_edit', array()),
                'list'      => array($this, 'action_list', array()),
                'add'       => array($this, 'action_add', array()),
            );

            $context['TPortal']['subaction'] = $subAction;

            TPAdmin::getInstance()->topMenu($subAction);
            TPAdmin::getInstance()->sideMenu($subAction);

            $action     = new \ElkArte\Action();
            $subAction  = $action->initialize($subActions, $subAction);
            $action->dispatch($subAction);
       }

    }}}

    public function action_delete() {{{


        self::action_list();
    }}}

    public function action_edit() {{{
		global $context;


        $context['sub_template'] 	    = 'edit_download';
    }}}

    public function action_list() {{{
        parent::make_list('download');

        $this->context['sub_template'] = 'list_download';
    }}}    

    public function action_add() {{{
		global $context;

		// Set the defaults
		$context['download_category']	= 1;
		$context['download_subject'] 	= '';
		$context['download_body'] 	    = '';
        $context['download_link']       = '';
        $context['download_status']		= 0;
        $context['download_categories']	= TPCategory::getInstance()->select(array('id', 'display_name'), array('item_type' => 'download'));

        $context['sub_template'] 	    = 'add_download';
    }}}

    public function list_download($start, $items_per_page, $sort) {{{
        return TPDownload::getInstance()->list($start, $items_per_page, $sort);
	}}}

	public function list_total_download() {{{
        return TPDownload::getInstance()->total();
	}}}

}

?>
