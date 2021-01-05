<?php
/**
 * @package TinyPortal
 * @version 1.0.0
 * @author TinyPortal - http://www.tinyportal.net
 * @license BSD 3.0 http://opensource.org/licenses/BSD-3-Clause/
 *
 * Copyright (C) 2020 - The TinyPortal Team
 *
 */

function template_main()
{
	global $context;

	if ($context['TPortal']['subaction'] == 'credits') {
		template_tpcredits();
    }
}

// Credits Page
function template_tpcredits()
{
	global $txt;

	echo '
	<div class="tborder">
		<div class="cat_bar">
			<h3 class="category_header">' . $txt['tp-credits'] . '</h3>
		</div><div></div>
		<p class="information">' , $txt['tp-creditack2']  , '</p>
		<div class="content">
			<span class="topslice"><span></span></span>
			<div class="content" style="line-height: 1.6em; padding: 0 1em;">
				'.$txt['tp-credit1'].'
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';
}

?>
