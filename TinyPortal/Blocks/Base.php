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
    protected $blockOptions = array();

    public function __construct() {{{
       global $context, $scripturl, $txt, $settings, $user_info, $maintenance, $modSettings;

        $this->context      = &$context;
        $this->scripturl    = $scripturl;
        $this->txt          = $txt;
        $this->settings     = $settings;
        $this->modSettings  = $modSettings;
        $this->user_info    = $user_info;
        $this->blockOptions = array ( 'panel' => '99' );

    }}}

    public function prepare(&$block) {{{

    }}}

    public function setup(&$block) {{{

    }}}

    public function display($block) {{{

    }}}

    public function admin_setup( &$block ) {{{

		if(empty($block['settings'])) {
			$block += $this->getDefaultBlockOptions();
		}

	}}}

    public function admin_display( $block ) {{{

		return false;

    }}}

	public function getDefaultBlockOptions() {{{

		return $this->blockOptions;

	}}}

	protected function setDefaultBlockOptions($options) {{{

		array_merge($this->blockOptions, $options);

	}}}

}

?>
