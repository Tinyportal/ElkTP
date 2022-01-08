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

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class BoardIndex extends \ElkArte\Controller\BoardIndex implements \ElkArte\FrontpageInterface
{

	public function action_index() {{{

		if( \TinyPortal\Model\Admin::getInstance()->getSetting('portal_type') == 'portal_guest' ) {
			$controller = new \ElkArte\Controller\Auth('action_kickguest');
			$controller->action_kickguest();
			obExit(null, true);
		}
		else {
			parent::action_index();
		}

	}}}

}

?>
