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

    public function setup( &$block ) {{{

        $block['title']     = '<span class="header">' . $block['title'] . '</span>';
        $block['use_rtf8']  = $block['var1'];

    }}}

    public function display( $block ) {{{

        echo '<div style="padding: 5px; ' , !empty($this->context['TPortal']['rsswidth']) ? 'max-width: ' . $this->context['TPortal']['rsswidth'] .';' : '' , '" class="middletext">' , \TinyPortal\Model\Subs::getInstance()->parseRSS($block['body'], $block['rss_utf8']) , '</div>';

    }}}

    public function admin_setup( &$block ) {{{

    }}}

    public function admin_display( $block ) {{{

		echo '
			<hr><dl class="tptitle settings">
				<dt>
					<label for="tp_block_body">' .	$this->txt['tp-rssblock'] . '</label>
				</dt>
				<dd>
					<input name="tp_block_body" id="tp_block_body" value="' .$this->context['TPortal']['blockedit']['body']. '" style="width: 95%">
				</dd>
				<dt>
					<label for="field_name">' , $this->txt['tp-rssblock-useutf8'].'</label>
				</dt>
				<dd>
					<input type="radio" name="tp_block_var1" value="1" ' , $this->context['TPortal']['blockedit']['var1']=='1' ? ' checked' : '' ,'>'.$this->txt['tp-utf8'].'<br>
					<input type="radio" name="tp_block_var1" value="0" ' , ($this->context['TPortal']['blockedit']['var1']=='0' || $this->context['TPortal']['blockedit']['var1']=='') ? ' checked' : '' ,'>'.$this->txt['tp-iso'].'
				</dd>
				<dt>
					<label for="field_name">' . $this->txt['tp-rssblock-showonlytitle'].'</label>
				</dt>
				<dd>
					<input type="radio" name="tp_block_var2" value="1" ' , $this->context['TPortal']['blockedit']['var2']=='1' ? ' checked' : '' ,'>'.$this->txt['tp-yes'].'
					<input type="radio" name="tp_block_var2" value="0" ' , ($this->context['TPortal']['blockedit']['var2']=='0' || $this->context['TPortal']['blockedit']['var2']=='') ? ' checked' : '' ,'>'.$this->txt['tp-no'], '
				</dd>
				<dt>
					<label for="tp_block_var3">' . $this->txt['tp-rssblock-maxwidth'].'</label>
				</dt>
				<dd>
					<input type="number" name="tp_block_var3" id="tp_block_var3" value="' , $this->context['TPortal']['blockedit']['var3'],'" style="width: 6em">
				</dd>
				<dt>
					<label for="tp_block_var4">' . $this->txt['tp-rssblock-maxshown'].'</label>
				</dt>
				<dd>
					<input type="number" name="tp_block_var4" id="tp_block_var4" value="' , $this->context['TPortal']['blockedit']['var4'],'" style="width: 6em">
				</dd>
			</dl>
		</div>';

		return true;

    }}}

}

?>
