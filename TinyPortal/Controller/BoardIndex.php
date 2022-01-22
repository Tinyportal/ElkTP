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

use \ElkArte\Errors\Errors;
use \ElkArte\sources\Frontpage_Interface;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class BoardIndex extends \BoardIndex_Controller implements Frontpage_Interface
{

	public function action_index() {{{
		global $context;

		if( (\TinyPortal\Model\Admin::getInstance()->getSetting('portal_type') == 'portal_guest') && $context['user']['is_guest'] ) {
			require_once CONTROLLERDIR . '/Auth.controller.php';
			$controller = new \Auth_Controller();
			$controller->action_kickguest();
			obExit(null, true);
		}
		else {
			parent::action_index();
		}

	}}}

}

?>
