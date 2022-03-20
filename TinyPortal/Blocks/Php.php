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

class Php extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $block['title'] = '<span class="header">' . $block['title'] . '</span>';

    }}}

    public function display( $block ) {{{

        if(!empty($block['body'])) {
            eval(\TinyPortal\Model\Subs::getInstance()->convertphp($block['body'], true));
        }

    }}}

    public function admin_setup( &$block ) {{{

    }}}

    public function admin_display( $block ) {{{

		echo '
			</div><div>
			<textarea style="width: 94%; margin: 0px 0px 10px;" name="tp_block_body" id="tp_block_body" rows="15" cols="40" wrap="auto">' ,  $this->context['TPortal']['blockedit']['body'] , '</textarea>
			<p><div class="tborder" style=""><p style="padding: 0 0 5px 0; margin: 0;">' , $this->txt['tp-blockcodes'] , ':</p>
				<select name="tp_blockcode" id="tp_blockcode" size="8" style="margin-bottom: 5px; width: 94%" onchange="changeSnippet(this.selectedIndex);">
					<option value="0" selected="selected">' , $this->txt['tp-none-'] , '</option>';
		if(!empty($this->context['TPortal']['blockcodes'])) {
			foreach($this->context['TPortal']['blockcodes'] as $bc) {
				echo '<option value="' , $bc['file'] , '">' , $bc['name'] , '</option>';
			}
		}

		echo '
				</select>
				<p style="padding: 10px 0 10px 0; margin: 0;"><input type="button" value="' , $this->txt['tp-insert'] , '" name="blockcode_save" onclick="submit();" />
				<input type="checkbox" name="blockcode_overwrite" value="' . $this->context['TPortal']['blockedit']['id'] . '" /> ' , $this->txt['tp-blockcodes_overwrite'] , '</p>
			</div>
		<div id="blockcodeinfo" class="description" >&nbsp;</div>
		<script type="text/javascript"><!-- // --><![CDATA[
			function changeSnippet(indx)
			{
				var snipp = new Array();
				var snippAuthor = new Array();
				var snippTitle = new Array();
				snipp[0] = "";
				snippAuthor[0] = "";
				snippTitle[0] = "";';

		$count=1;
		foreach($this->context['TPortal']['blockcodes'] as $bc) {
			$what = str_replace(array(",",".","/","\n"),array("&#44;","&#46;","&#47;",""), $bc['text']);
			echo '
				snipp[' . $count . '] = "<div>' . $what . '</div>";
				snippTitle[' . $count . '] = "<h3 style=\"margin: 0 0 5px 0; padding: 0;\">' . $bc['name'].' <span style=\"font-weight: normal;\">' . $this->txt['tp-by'] . '</span> ' . $bc['author'] . '</h3>";
				';
				$count++;
		}

		echo '
				setInnerHTML(document.getElementById("blockcodeinfo"), snippTitle[indx] + snipp[indx]);
			}
		// ]]></script>';

		return true;

    }}}

}

?>
