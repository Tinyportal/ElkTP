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

// ** Sections **
// Search Articles Page
// Article search results Page

// Search Articles Page
function template_article_search_form()
{
	global $context, $settings, $txt, $scripturl;

	echo '
	<form accept-charset="', 'UTF-8', '" name="TPsearcharticle" action="' . $scripturl . '?action=tpsearch;sa=searchresults" method="post">
		<div class="tborder">
			<div class="cat_bar"><header class="category_header">' , $txt['tp-searcharticles2'] , '</header></div>
			<span class="upperframe"></span>
			<div class="roundframe">
				<div class="tp_pad">'.$txt['tp-searcharticleshelp'].'</div>
				<div class="tp_pad">
					<b>'.$txt['tp-search'].':</b><br>
					<input type="text" id="searchbox" name="tpsearch_what" required><br>
					<input type="checkbox" name="tpsearch_title" checked="checked"> '.$txt['tp-searchintitle'].'<br>
					<input type="checkbox" name="tpsearch_body" checked="checked"> '.$txt['tp-searchinbody'],'<br>
					<input type="hidden" name="sc" value="' , $context['session_id'] , '"><br>
					<input type="submit" name="'.$txt['tp-search'].'" value="'.$txt['tp-search'].'">
				</div>';

		if ($context['TPortal']['fulltextsearch']==1) {
			echo '
				<div class="tp_pad">'.$txt['tp-searcharticleshelp2'].'</div>';
		}
	echo '
			</div>
			<span class="lowerframe"></span>
		</div>
	</form>';
}

// Article search results Page
function template_article_search_results()
{

	global $context, $settings, $txt, $scripturl;

	echo '
	<div class="tborder">
		<div class="cat_bar">
			<header class="category_header">' , $txt['tp-searchresults'] , '
			' . $txt['tp-searchfor'] . '  &quot;'.$context['TPortal']['searchterm'].'&quot;</header>
		</div>
		<span class="upperframe"></span>
		<div class="roundframe">
			<div class="tp_pad">'.$txt['tp-searcharticleshelp'].'</div>
				<div class="tp_pad">
				<form accept-charset="', 'UTF-8', '"  name="TPsearcharticle" action="' . $scripturl . '?action=tpsearch;sa=searchresults" method="post">
				<div class="tp_pad">
					<b>'.$txt['tp-search'].':</b><br>
					<input type="text" id="searchbox" name="tpsearch_what" value="'.$context['TPortal']['searchterm'].'" required><br>
					<input type="checkbox" name="tpsearch_title" checked="checked"> '.$txt['tp-searchintitle'].'<br>
					<input type="checkbox" name="tpsearch_body" checked="checked"> '.$txt['tp-searchinbody'],'<br>
					<input type="hidden" name="sc" value="' , $context['session_id'] , '"><br>
					<input type="submit" name="'.$txt['tp-search'].'" value="'.$txt['tp-search'].'">
				</div>';

		if ($context['TPortal']['fulltextsearch']==1) {
		echo '
				<div class="tp_pad">'.$txt['tp-searcharticleshelp2'].'</div>';
		}
	echo '
				</form>
			</div>
		</div>
		<span class="lowerframe"></span>
	</div>
	';
	$bb = 1;
	foreach($context['TPortal']['searchresults'] as $res) {
		echo '
			<div class="windowbg padding-div" style="margin-bottom:3px;">
				<h4 class="tpresults"><a href="' . $scripturl . '?page=' . $res['id'] . '">' . $res['subject'] . '</a></h4>
				<hr>
				<div class="tpresults" style="padding-top: 4px;">
					<div class="middletext">' , $res['body'] . '</div>
					<div class="smalltext" style="padding-top: 0.4em;">' , $txt['tp-by'] . ' ' . $res['author'] . ' - ', standardTime($res['date']) , '</div>
				</div>
			</div>';
		$bb++;
	}
	echo '
	<div class="pagesection">
		<span>', $context['page_index'], '</span>
	</div>';
}

?>
