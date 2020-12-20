<?php
/**
 * @package TinyPortal
 * @version 1.0.0
 * @author IchBin - http://www.tinyportal.net
 * @founder Bloc
 * @license MPL 2.0
 *
 * The contents of this file are subject to the Mozilla Public License Version 2.0
 * (the "License"); you may not use this package except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Copyright (C) 2020 - The TinyPortal Team
 *
 */
use \TinyPortal\Article as TPArticle;
use \TinyPortal\Block as TPBlock;
use \TinyPortal\Integrate as TPIntegrate;
use \TinyPortal\Mentions as TPMentions;
use \TinyPortal\Util as TPUtil;
use ElkArte\Errors\Errors;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class TPortalAdmin_Controller extends Action_Controller
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
