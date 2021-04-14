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
namespace TinyPortal\Model;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Portal
{
    private static $_instance   = null;

    public static function getInstance() {{{

    	if(self::$_instance == null) {
			self::$_instance = new self();
		}

    	return self::$_instance;

    }}}

    // Empty Clone method
    private function __clone() { }

    // TinyPortal init
    public function init() {{{
        global $context, $txt, $user_info, $settings, $modSettings;

        \call_integration_hook('integrate_tp_pre_init');

        // has init been run before? if so return!
        if(isset($context['TPortal']['redirectforum'])) {
            return;
        }

        if(Subs::getInstance()->loadLanguage('TPortal') == false) {
            Subs::getInstance()->loadLanguage('TPortal', 'english');
        }

        Mentions::getInstance()->addJS();

        $context['TPortal'] = array();

        if(!isset($context['forum_name'])) {
            $context['forum_name'] = '';
        }

        $context['TPortal'] = array();
        // Add all the TP settings into ['TPortal']
        Subs::getInstance()->setupSettings();
        // Setup querystring
        $context['TPortal']['querystring'] = $_SERVER['QUERY_STRING'];

        // go back on showing attachments..
        if(isset($_GET['action']) && $_GET['action'] == 'dlattach') {
            return;
        }

        // Grab the SSI for its functionality
        require_once(BOARDDIR. '/SSI.php');

        // set up the layers, but not for certain actions
        if(!isset($_REQUEST['preview']) && !isset($_REQUEST['quote']) && !isset($_REQUEST['xml']) && !isset($aoptions['nolayer'])) {
            \Template_Layers::getInstance()->add($context['TPortal']['hooks']['tp_layer']);
        }

        \loadTemplate('TPsubs');
        \loadTemplate('TPBlockLayout');

        // is the permanent theme option set?
        if(isset($_GET['permanent']) && !empty($_GET['theme']) && $context['user']['is_logged']) {
            Subs::getInstance()->permaTheme($_GET['theme']);
        }

        // Load the stylesheet stuff
        Subs::getInstance()->loadCSS();

        // if we are in permissions admin section, load all permissions
        if((isset($_GET['action']) && $_GET['action'] == 'permissions') || (isset($_GET['area']) && $_GET['area'] == 'permissions')) {
            Permissions::getInstance()->collectPermissions();
        }

        // finally..any errors finding an article or category?
        if(!empty($context['art_error'])) {
            throw new \Elk_Exception($txt['tp-articlenotexist'], 'general');
        }

        if(!empty($context['cat_error'])) {
            throw new \Elk_Exception($txt['tp-categorynotexist'], 'general');
        }

        \call_integration_hook('integrate_tp_post_init');

        // set cookie change for selected upshrinks
        Subs::getInstance()->setupUpshrinks();

    }}}

}

?>
