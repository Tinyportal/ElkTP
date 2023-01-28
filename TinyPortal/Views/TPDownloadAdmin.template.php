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

// New Download
function template_add_download()
{
	global $context, $scripturl, $txt, $boardurl;

	echo '<link rel="stylesheet" type="text/css" href="'.$boardurl.'/TinyPortal/Views/css/pell.css">
		<h2 class="category_header">'.$txt['tp-upload'].'</h2>
		<div class="forumposts">
			<form id="download_form_edit" action="'.$scripturl.'?action=admin;area=tpdownload;sa=edit" value="Submit" method="post" accept-charset="UTF-8" enctype="multipart/form-data">';

			if(isset($context['download_id'])) {
				echo '<input type="hidden" name="id" value="'.$context['download_id'].'" />';
			}

			echo '<dl id="post_header">
				<dt class="clear"><label for="post_subject" id="caption_subject">Subject:</label></dt>';

			if(!empty($context['download_subject'])) {
				echo '<dd><input type="text" name="download_subject" value="'.$context['download_subject'].'" tabindex="1" size="80" maxlength="80" class="input_text" placeholder="Subject" required="required" /><br /></dd>';
			}
			else {
				echo '<dd><input type="text" name="download_subject" value="" tabindex="1" size="80" maxlength="80" class="input_text" placeholder="Subject" required="required" /></dd>';
			}
			echo '<dt class="clear"><label for="download_category">Downloads Category:</label></dt>';

			echo '<dd><select name="download_category">';
			if(!empty($context['download_categories']) && is_array($context['download_categories'])) {
				foreach($context['download_categories'] as $k => $v) {
					if($v['id'] == $context['download_category']) {
						echo '<option value="'.$v['id'].'" selected>'.$v['display_name'].'</option>';
					}
					else {
						echo '<option value="'.$v['id'].'">'.$v['display_name'].'</option>';
					}
				}
			}
			echo '</select></dd>
			<dt class="clear"><label for="download_status">Status:</label></dt>
			<dd><select name="download_status">';
			foreach( array( 0 => $txt['tp-disabled'] , 1 => $txt['tp-enabled'], 2 => $txt['tp-approval'] ) as $k => $v) {
				if($k == $context['download_status']) {
					echo '<option value="'.$k.'" selected>'.$v.'</option>';
				}
				else {
					echo '<option value="'.$k.'">'.$v.'</option>';
				}
			}
			echo '</select></dd>
			</dl>
			<input type="hidden" id="download_body" name="download_body" />
			<div id="editor_toolbar_container">
				<div id="eb_editor" class="eb_editor"></div>
			</div>
			<div id="post_confirm_buttons" class="submitbutton">
                <div style="float: left;">
                    <input type="file" id="download_link" name="download_link" />
                </div>
                <div style="float: right;">
					<input type="submit" value="Submit">
                </div>
			</div>
        <input type="hidden" name="'.$context['session_var'].'" value="'.$context['session_id'].'" />
		</form>
	</div>
	<script src="'.$boardurl.'/TinyPortal/Views/scripts/pell.js"></script>
	<script>
	var editor = window.pell.init({
		element: document.getElementById(\'eb_editor\'),
		defaultParagraphSeparator: \'p\',
		styleWithCSS: false,
		onChange: function (html) {
			document.getElementById(\'download_body\').value = html
		}
	})
	';
	if(!empty($context['download_body'])) {
		echo 'editor.content.innerHTML = '.JavaScriptEscape($context['download_body']);
	}
	echo '</script>';

    if(!empty($context['download_link_src'])) {
        echo '<a href="'. $context['download_link_src'] .'" download>'.$txt['tp-download'].'</a>';
    }
}

function template_edit_download()
{


}

function template_list_download()
{
    template_show_list('download_list');
}

?>
