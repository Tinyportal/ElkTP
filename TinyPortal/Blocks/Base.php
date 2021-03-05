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
namespace TinyPortal\Blocks;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Base
{
    protected $context;
    protected $scripturl;
    protected $txt;

    public function __construct() {{{
       global $context, $scripturl, $txt;

        $this->context      = &$context;
        $this->scripturl    = $scripturl;
        $this->txt          = $txt;

    }}}

}

?>
