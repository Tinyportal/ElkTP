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

class Theme extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $block['title'] = '<span class="header">' . $block['title'] . '</span>';
        $this->context['TPortal']['themeboxbody'] = $block['body'];

    }}}

    public function display( $block ) {{{

        $what = explode(',', $this->context['TPortal']['themeboxbody']);
        $temaid = array();
        $temanavn = array();
        $temapaths = array();
        foreach($what as $wh => $wht) {
            $all = explode('|', $wht);
            if($all[0] > -1) {
                $temaid[] = $all[0];
                $temanavn[] = isset($all[1]) ? $all[1] : '';
                $temapaths[] = isset($all[2]) ? $all[2] : '';
            }
        }

        if(isset($this->context['TPortal']['querystring'])) {
            $tp_where = \TinyPortal\Model\Util::htmlspecialchars(strip_tags($this->context['TPortal']['querystring']));
        }
        else {
            $tp_where = 'action=forum';
        }

        if($tp_where != '') {
            $tp_where .= ';';
        }

        // remove multiple theme=x in the string.
        $tp_where = preg_replace("'theme=[^>]*?;'si", "", $tp_where);

        if(is_countable($temaid) && count($temaid) > 0) {
            echo '
                <form name="jumpurl1" onsubmit="return jumpit()" class="middletext" action="" style="padding: 0; margin: 0; text-align: center;">
                <select style="width: 100%; margin: 5px 0px 5px 0px;" size="1" name="jumpurl2" onchange="check(this.value)">';
            for($a = 0; $a < (count($temaid)); $a++) {
                echo '
                    <option value="'.$temaid[$a].'" ', $this->settings['theme_id'] == $temaid[$a] ? 'selected="selected"' : '' ,'>'.substr($temanavn[$a],0,20).'</option>';
            }
            echo '
                </select><br>' , $this->context['user']['is_logged'] ?
                '<input type="checkbox" value=";permanent" onclick="realtheme()" /> '. $this->txt['tp-permanent']. '<br>' : '' , '<br>
                <input type="button" class="button_submit" value="'.$this->txt['tp-changetheme'].'" onclick="jumpit()" /><br><br>
                <input type="hidden" value="'.\TinyPortal\Model\Util::htmlspecialchars($this->scripturl . '?'.$tp_where.'theme='.$this->settings['theme_id']).'" name="jumpurl3" />
                <div style="text-align: center; width: 95%; overflow: hidden;">';

            echo ' <img src="'.$this->settings['images_url'].'/thumbnail.png" alt="" id="chosen" name="chosen" style="max-width: 100%;" />';

            echo '
                </div>
                </form>
                <script type="text/javascript"><!-- // --><![CDATA[
                var extra = \'\';
            var themepath = new Array();';
            for($a = 0; $a < (count($temaid)); $a++){
                echo '
                    themepath['.$temaid[$a].'] = "'.$temapaths[$a].'/thumbnail.gif";
                ';
            }

            echo '
                function jumpit()
                {
                    window.location = document.jumpurl1.jumpurl3.value + extra;
                    return false;
                }
            function realtheme()
            {
                if (extra === ";permanent")
                    extra = "";
                else
                    extra = ";permanent";
            }
            function check(icon)
            {
                document.chosen.src= themepath[icon]
                    document.jumpurl1.jumpurl3.value = \'' . $this->scripturl . '?'. $tp_where.'theme=\' + icon
            }
            // ]]></script>';
        }
        else {
            echo $this->txt['tp-nothemeschosen'];
        }


    }}}

    public function admin_setup( &$block ) {{{

		parent::admin_setup($block);

    }}}

    public function admin_display( $block ) {{{
		// get the ids
		$myt = array();
		$thems	= explode(",", $block['body']);
		foreach($thems as $g => $gh) {
			$wh = explode("|",$gh);
			$myt[] = $wh[0];
		}

		echo '
			<hr><input type="hidden" name="blockbody' .$block['id']. '" value="' .$block['body'] . '" />
			<div style="padding: 5px;">
				<div style="max-height: 25em; overflow: auto;">
					<input type="hidden" name="tp_theme-1" value="-1">
					<input type="hidden" name="tp_tpath-1" value="1">';
					foreach($this->context['TPthemes'] as $tema) {
					echo '
						<img class="theme_icon" alt="*" src="'.$tema['path'].'/thumbnail.png" /> <input type="checkbox" name="tp_theme'.$tema['id'].'" value="'.$tema['name'].'"';
						if(in_array($tema['id'], $myt)) {
							echo ' checked';
						}
						echo '>'.$tema['name'].'<input type="hidden" value="'.$tema['path'].'" name="tp_path'.$tema['id'].'"><br>';
		}

		echo '
				</div>
			</div>
			<input type="checkbox" onclick="invertAll(this, this.form, \'tp_theme\');" /> '.$this->txt['tp-checkall'],'
		';

    }}}

}

?>
