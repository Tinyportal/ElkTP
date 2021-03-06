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
namespace TinyPortal\Blocks;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Base
{
    protected $context;
    protected $scripturl;
    protected $txt;
    protected $settings;
    protected $user_info;
    protected $maintenance;

    public function __construct() {{{
       global $context, $scripturl, $txt, $settings, $user_info, $maintenance, $modSettings;

        $this->context      = &$context;
        $this->scripturl    = $scripturl;
        $this->txt          = $txt;
        $this->settings     = $settings;
        $this->modSettings  = $modSettings;
        $this->user_info    = $user_info;

    }}}

}

?>
