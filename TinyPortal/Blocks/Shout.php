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

use TinyPortal\Model\Shout as TPShout;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Shout extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function prepare( &$block ) {{{

    }}}

    public function setup( &$block ) {{{

    }}}

    public function display( $block ) {{{

        echo '<div id="shoutboxContainer">
                <div class="middletext" style="width: 100%; height: 250px; overflow: auto;">
                    <div class="tp_shoutframe">
                        <div class="shoutbody_layout" style="background:#f0f4f7;">
                        <div class="showhover">
                            <div class="shoutbox_time">
                                <span class="smalltext" style="color:#787878;">2021 Mar 06 20:27:03</span>
                            </div>
                            <div class="shoutbox_edit">
                            </div>
                            <b><a style="color:#FF0000;" href="http://192.168.0.70/SMF2.1/index.php?action=profile;u=1">admin</a></b>: <span style="color:#000">Another one</span>
                            <p class="clearthefloat"></p>
                        </div>
                    </div>
                </div>
            </div>';
            
        echo '	
            <form accept-charset="UTF-8" class="smalltext" name="tp_shoutbox" id="tp_shoutbox" action="'.$this->scripturl.'?action=tportal;sa=shout" method="post"><hr>
                <div style="margin-bottom: 5px;">
                    <input type="text" id="tp_shout" class="shoutbox_input" name="tp_shout" maxlength="256" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);" onchange="storeCaret(this);" tabindex="1">
                    <input onclick="TPupdateShouts(\'save\', 2 , null , 2 ); return false;" type="submit" name="shout_send" value="&nbsp;Shout!&nbsp;" tabindex="2" class="button_submit">
                </div>
                <input type="hidden" id="tp_shout_id" name="tp_shout_id" value="'.$block['id'].'">
                <input type="hidden" name="sc" value="'.$this->context['session_id'].'">
            </form>';

        echo '
            <!--shoutboxContainer-->
            </div>';

    }}}

    public function admin( &$block ) {{{

    }}}
}

?>
