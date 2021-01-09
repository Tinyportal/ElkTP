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

use \TinyPortal\Model\Article as TPArticle;
use \TinyPortal\Model\Block as TPBlock;
use \TinyPortal\Model\Integrate as TPIntegrate;
use \TinyPortal\Model\Mentions as TPMentions;
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

        // Wrap around the old TinyPortal logic for now
        require_once(SOURCEDIR . '/TPortalAdmin.php');
        TPortalAdmin();

    }}}

}

?>
