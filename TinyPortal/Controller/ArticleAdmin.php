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

use \TinyPortal\Model\Database as TPDatabase;
use \TinyPortal\Model\Util as TPUtil;
use \ElkArte\Errors\Errors;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class ArticleAdmin extends \Action_Controller
{

    public function action_index() {{{

        if(\loadLanguage('TPmodules') == false) {
            \loadLanguage('TPmodules', 'english');
        }

        require_once(SUBSDIR . '/Action.class.php');
        $subActions = array (
            'searcharticle' => array($this, 'action_search', array()),
            'searchresults' => array($this, 'action_results', array()),
        );

        $sa = TPUtil::filter('sa', 'get', 'string');

        $action     = new \Action();
        $subAction  = $action->initialize($subActions, $sa);
        $action->dispatch($subAction);

    }}}

}

?>
