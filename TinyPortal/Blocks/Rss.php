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

class Rss extends Base
{

    public function __construct() {{{
        parent::__construct();

	}}}

	public function prepare( &$block ) {{{

    }}}

    public function setup( &$block ) {{{

        $block['title']     = '<span class="header">' . $block['title'] . '</span>';
        $block['use_rtf8']  = $block['utf'];

    }}}

    public function display( $block ) {{{

        echo '<div style="padding: 5px; ' , !empty($this->context['TPortal']['display_width']) ? 'max-width: ' . $this->context['TPortal']['display_width'] .';' : '' , '" class="middletext">' , \TinyPortal\Model\Subs::getInstance()->parseRSS($block['body'], $block['utf']) , '</div>';

    }}}

    public function admin_setup( &$block ) {{{

		$default = $this->getDefaultBlockOptions() + array(
			'utf'			=> 0,
			'display_title' => 1,
			'display_width'	=> 50,
			'display_max'	=> 10,
		);

		$block['settings'] = isset($block['settings']) ? $block['settings'] : json_encode($default);
		$block += $default;

    }}}

    public function admin_display( $block ) {{{

		echo '
			<hr><dl class="tptitle settings">
				<dt>
					<label for="tp_block_body">' .	$this->txt['tp-rssblock'] . '</label>
				</dt>
				<dd>
					<input name="tp_block_body" id="tp_block_body" value="' .$block['body']. '" style="width: 95%">
				</dd>
				<dt>
					<label for="field_name">' , $this->txt['tp-rssblock-useutf8'].'</label>
				</dt>
				<dd>
					<input type="radio" name="tp_block_set_utf" value="1" ' , $block['utf']=='1' ? ' checked' : '' ,'>'.$this->txt['tp-utf8'].'<br>
					<input type="radio" name="tp_block_set_utf" value="0" ' , ($block['utf']=='0' || $block['utf']=='') ? ' checked' : '' ,'>'.$this->txt['tp-iso'].'
				</dd>
				<dt>
					<label for="field_name">' . $this->txt['tp-rssblock-showonlytitle'].'</label>
				</dt>
				<dd>
					<input type="radio" name="tp_block_set_display_title" value=1' , ($block['display_title'] == '1') ? ' checked' : '' ,'>'.$this->txt['tp-yes'].'
					<input type="radio" name="tp_block_set_display_title" value=0' , ($block['display_title'] != '1') ? ' checked' : '' ,'>'.$this->txt['tp-no'].'
				</dd>
				<dt>
					<label for="tp_block_set_display_width">' . $this->txt['tp-rssblock-maxwidth'].'</label>
				</dt>
				<dd>
					<input type="number" name="tp_block_set_display_width" id="tp_block_set_display_width" value="' , $block['display_width'],'" style="width: 6em">
				</dd>
				<dt>
					<label for="tp_block_set_display_max">' . $this->txt['tp-rssblock-maxshown'].'</label>
				</dt>
				<dd>
					<input type="number" name="tp_block_set_display_max" id="tp_block_set_display_max" value="' , $block['display_max'],'" style="width: 6em">
				</dd>
			</dl>
		</div>';

		return true;

    }}}

}

?>
