<?php
/**
 * @package TinyPortal
 * @version 1.0.0
 * @author TinyPortal - http://www.tinyportal.net
 * @license BSD 3.0 http://opensource.org/licenses/BSD-3-Clause/
 *
 * Copyright (C) 2020 - The TinyPortal Team
 *
 */
use \TinyPortal\Util as TPUtil;

if (!defined('ELK')) {
        die('Hacking attempt...');
}

// TinyPortal module entrance
function TPCredits()
{
	tp_hidebars();
	$context['TPortal']['not_forum'] = false;

	if(loadLanguage('TPhelp') == false)
		loadLanguage('TPhelp', 'english');

	loadtemplate('TPhelp');
}
?>
