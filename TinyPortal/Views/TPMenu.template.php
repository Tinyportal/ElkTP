<?php
/**
 * @package TinyPortal
 * @version 1.0.0 RC3
 * @author TinyPortal - http://www.tinyportal.net
 * @license BSD 3.0 http://opensource.org/licenses/BSD-3-Clause/
 *
 * Copyright - The TinyPortal Team
 *
 */
use \TinyPortal\Model\Subs as TPSubs;

// New Menu
function template_new_menu()
{
    template_tp_menu('add');
}

// Edit existing menu
function template_edit_menu()
{
    template_tp_menu('edit');
}

// List existing menu
function template_list_menu()
{
    template_show_list('menu_list');
}

function template_tp_menu( $type )
{
    global $context, $scripturl, $txt;

    echo '
        <form action="', $scripturl, '?action=admin;area=tpmenu;sa=save" method="post" accept-charset="UTF-8" name="menu" id="menu" class="flow_hidden">
            <input type="hidden" name="sc" value="', $context['session_id'], '" />
            <input type="hidden" name="tpadmin_form" value="'.$type.'">';
            if(isset($context['TPortal']['menu']['id'])) {
                echo '<input type="hidden" name="id" value="'.$context['TPortal']['menu']['id'].'">';
            }
    echo '
            <div class="category_header">
                <h3>
                    ', $context['page_title'], '
                </h3>
            </div>
            <div class="roundframe">
                <dl class="settings tptitle">
				<dt>
					<label for="tp_menu_type">'.$txt['tp-type'].'</label>
				</dt>
				<dd>
					<select size="1" name="tp_menu_type" id="tp_menu_type">';
                        foreach($context['TPortal']['menu']['types'] as $type) {
						    echo '<option value="'.$type.'" ',  $context['TPortal']['menu']['type'] == $type ? 'selected' : '', '>'.$txt['tp-'.$type].'</option>';
                        }
					echo '
                    </select>
				</dd>
			</dl>
            </div>
            <div class="submitbutton">
                <input name="submit" value="', $txt['tp-submit'], '" class="button_submit" type="submit" />
            </div>
        </form>';

}
?>
