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

// ** Sections ** (ordered like in the admin screen):
// General Settings page
// Frontpage Settings page
// Article Categories page
// Edit Article Category Page
// Add category Page
// Category List Page
// Articles page
// Articles in category Page
// Uncategorized articles Page
// Article Submissions Page
// Article Settings Page
// Article icons Page
// Panel Settings Page
// Block Settings Page
// Add Block Page
// Block Access Page
// Menu Manager Page
// Menu Manager Page: single menus
// Add Menu / Add Menu item Page
// Edit menu item Page

function getElementById($id,$url){

$html = new DOMDocument();
$html->loadHtmlFile($url); //Pull the contents at a URL, or file name

$xpath = new DOMXPath($html); // So we can use XPath...

return($xpath->query("//*[@id='$id']")->item(0)); // Return the first item in element matching our id.

}

// General Settings page
function template_settings()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

	echo '
	<form accept-charset="UTF-8" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tpsettings;sa=updatesettings" method="post">
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="tpadmin_form" value="settings">
		<div class="cat_bar"><header class="category_header">' . $txt['tp-generalsettings'] . '</header></div>
		<p class="description">' , $txt['tp-helpsettings'] , '</p>
		<div id="settings" class="generic_list_wrapper">
			<div class="content">
				<div class="formtable padding-div">
						<!-- START non responsive themes form -->
					<dl class="settings">
						<dt>
					       <label for="tp_resp">'.$txt['tp-formres'].'</label>
						</dt>
						<dd>';
						       $tm=explode(",",$context['TPortal']['resp']);
							foreach($context['TPallthem'] as $them) {
									echo '
										  <img class="theme_icon" alt="*" src="'.$them['path'].'/thumbnail.png" />
										  <input name="tp_resp'.$them['id'].'" id="tp_resp'.$them['id'].'" type="checkbox" value="'.$them['id'].'" ';
					              if(in_array($them['id'],$tm)) {
					                echo ' checked="checked" ';
					              }
					              echo '><label for="tp_resp'.$them['id'].'">'.$them['name'].'</label><br>';
						       }
						   echo '
							<br><input type="checkbox" name="tp_resp" id="tp_resp" value="0"><label for="tp_resp">'.$txt['tp-deselectthemes'].'</label>
						</dd>
					</dl>
					<div class="submitbutton"><input type="submit" value="'.$txt['tp-send'].'" name="'.$txt['tp-send'].'"></div>
					<!-- END non responsive themes form -->
							<br><hr>

				<dl class="settings">
					<dt>
						<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-frontpagetitle2'],'" onclick="return reqOverlayDiv(this.href);"></a>
						<label for="tp_frontpage_title">', $txt['tp-frontpagetitle'], '</label>
					</dt>
					<dd>
						<input type="text" name="tp_frontpage_title" id="tp_frontpage_title" value="' , !empty($context['TPortal']['frontpage_title']) ? $context['TPortal']['frontpage_title'] : '' , '" size="50">
					</dd>
					<dt>
						<label for="field_name">', $txt['tp-redirectforum'], '</label>
					</dt>
					<dd>
						<input type="radio" name="tp_redirectforum" id="tp_redirectforum1" value="1" ' , $context['TPortal']['redirectforum']=='1' ? 'checked' : '' , '><label for="tp_redirectforum1"> '.$txt['tp-redirectforum1'].'</label>
					</dd>
					<dd>
						<input type="radio" name="tp_redirectforum" id="tp_redirectforum2" value="0" ' , $context['TPortal']['redirectforum']=='0' ? 'checked' : '' , '><label for="tp_redirectforum2"> '.$txt['tp-redirectforum2'].'</label>
					</dd>
					<dt>
						<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-hideadminmenudesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
						<label for="tp_hideadminmenu">', $txt['tp-hideadminmenu'], '</label>
					</dt>
					<dd>
						<input type="checkbox" id="tp_hideadminmenu" name="tp_hideadminmenu" value="1" ' , $context['TPortal']['hideadminmenu']=='1' ? 'checked' : '' , '>
					</dd>
					<dt>
						<label for="tp_portal_type">', $txt['tp-portaltype'], '</label>
					</dt>
					<dd>
						<select name="tp_portal_type" id="tp_portal_type">
							<option value="portal_guest"', $context['TPortal']['portal_type'] == 'portal_guest' ? ' selected="selected"' : '', '>', $txt['tp-guestaccess'] ,'</option>
							<option value="portal"', $context['TPortal']['portal_type'] == 'portal' ? ' selected="selected"' : '', '>', $txt['tp-guestonly'], '</option>
							<option value="standalone"', $context['TPortal']['portal_type'] == 'standalone' ? ' selected="selected"' : '', '>', $txt['tp-standalone'], '</option>
						</select> 	
					</dd>
				</dl>
					<hr>
				<dl class="settings">
					<dt>
						<label for="tp_useroundframepanels">', $txt['tp-useroundframepanels'], '</label>
					</dt>
					<dd>
						<input type="checkbox" id="tp_useroundframepanels" name="tp_useroundframepanels" value="1" ' , $context['TPortal']['useroundframepanels']=='1' ? 'checked' : '' , '>
					</dd>
					<dt>
						<label for="tp_showcollapse">', $txt['tp-hidecollapse'], '</label>
					</dt>
					<dd>
						<input type="checkbox" id="tp_showcollapse" name="tp_showcollapse" value="1" ' , $context['TPortal']['showcollapse']=='1' ? 'checked' : '' , '>
					</dd>
					<dt>
						<label for="tp_blocks_edithide">', $txt['tp-hideediticon'], '</label>
					</dt>
					<dd>
						<input type="checkbox" id="tp_blocks_edithide" name="tp_blocks_edithide" value="1" ' , $context['TPortal']['blocks_edithide']=='1' ? 'checked' : '' , '>
					</dd>
					<dt>
						<label for="tp_uselangoption">', $txt['tp-uselangoption'], '</label>
					</dt>
					<dd>
						<input type="checkbox" id="tp_uselangoption" name="tp_uselangoption" value="1" ' , $context['TPortal']['uselangoption']=='1' ? 'checked' : '' , '>
					</dd>
					<dt>
						<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-use_groupcolordesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
						<label for="tp_use_groupcolor">', $txt['tp-use_groupcolor'], '</label>
					</dt>
					<dd>
						<input type="checkbox" id="tp_use_groupcolor" name="tp_use_groupcolor" value="1" ' , $context['TPortal']['use_groupcolor']=='1' ? 'checked' : '' , '>
					</dd>
				</dl>
					<hr>
				<dl class="settings">
					<dt>
						<label for="tp_maxstars">', $txt['tp-maxrating'], '</label>
					</dt>
					<dd>
						<input type="number" id="tp_maxstars" name="tp_maxstars" value="'.$context['TPortal']['maxstars'].'" style="width: 6em" min="1" max="10" step="1">
					</dd>
					<dt>
						<label for="tp_showstars">', $txt['tp-stars'], '</label>
					</dt>
					<dd>
						<input type="checkbox" id="tp_showstars" name="tp_showstars" value="1" ' , $context['TPortal']['showstars']=='1' ? 'checked' : '' , '>
					</dd>
				</dl>
					<hr>
				<dl class="settings">
					<dt>
						<label for="tp_oldsidebar">', $txt['tp-useoldsidebar'], '</label>
					</dt>
					<dd>
						<input type="checkbox" id="tp_oldsidebar" name="tp_oldsidebar" value="1" ' , $context['TPortal']['oldsidebar']=='1' ? 'checked' : '' , '>
					</dd>
					<dt>
						<label for="tp_admin_showblocks">', $txt['tp-admin_showblocks'], '</label>
					</dt>
					<dd>
						<input type="checkbox" id="tp_admin_showblocks" name="tp_admin_showblocks" value="1" ' , $context['TPortal']['admin_showblocks']=='1' ? 'checked' : '' , '>
					</dd>
					<dt>
						<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-imageproxycheckdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
						<label for="tp_imageproxycheck">', $txt['tp-imageproxycheck'], '</label>
					</dt>
					<dd>
						<input type="checkbox" id="tp_imageproxycheck" name="tp_imageproxycheck" value="1" ' , $context['TPortal']['imageproxycheck'] == '1' ? 'checked' : '' , '>
					</dd>';
                    $db = database();
                    if(version_compare($db->server_version(), '5.6', '>=')) {
                        echo '
                        <dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-fulltextsearchdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="tp_fulltextsearch">', $txt['tp-fulltextsearch'], '</label>
                        </dt>
                        <dd>
                            <input type="checkbox" id="tp_fulltextsearch" name="tp_fulltextsearch" value="1" ' , $context['TPortal']['fulltextsearch']=='1' ? 'checked' : '' , '>
                        </dd>';
                    }
					echo '
					<dt>
						<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-disabletemplateevaldesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
						<label for="tp_disable_template_eval">', $txt['tp-disabletemplateeval'], '</label>
					</dt>
					<dd>
                        <input type="checkbox" id="tp_disable_template_eval" name="tp_disable_template_eval" value="1" ' , $context['TPortal']['disable_template_eval']=='1' ? 'checked' : '' , '>
					</dd>
                    <dt>
						<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-imageuploadpathdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
						<label for="tp_image_upload_path">', $txt['tp-imageuploadpath'], '</label>
					</dt>
					<dd>
						<input type="text" id="tp_image_upload_path" name="tp_image_upload_path" value="' , !empty($context['TPortal']['image_upload_path']) ? $context['TPortal']['image_upload_path'] : '' , '" size="50">
					</dd>';
/*
                    <dt>
						<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-downloaduploadpathdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
						<label for="tp_download_upload_path">', $txt['tp-downloaduploadpath'], '</label>
					</dt>
					<dd>
						<input type="text" id="tp_download_upload_path" name="tp_download_upload_path" value="' , !empty($context['TPortal']['download_upload_path']) ? $context['TPortal']['download_upload_path'] : '' , '" size="50">
					</dd>
                    <dt>
						<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-blockcodeuploadpathdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
						<label for="tp_blockcode_upload_path">', $txt['tp-blockcodeuploadpath'], '</label>
					</dt>
					<dd>
						<input type="text" id="tp_blockcode_upload_path" name="tp_blockcode_upload_path" value="' , !empty($context['TPortal']['blockcode_upload_path']) ? $context['TPortal']['blockcode_upload_path'] : '' , '" size="50" >
					</dd>
*/
                    echo '<dt>
						<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-copyrightremovaldesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
						<label for="tp_copyrightremoval">', $txt['tp-copyrightremoval'], '</label>
					</dt>
					<dd>
						<input type="text" name="tp_copyrightremoval" id="tp_copyrightremoval" value="' , !empty($context['TPortal']['copyrightremoval']) ? $context['TPortal']['copyrightremoval'] : '' , '" size="50">
					</dd>
				</dl>
					<div class="submitbutton"><input type="submit" value="'.$txt['tp-send'].'" name="'.$txt['tp-send'].'"></div>
				</div>
			</div>
		</div>
	</form>';
}

// Frontpage Settings page
function template_frontpage()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

		echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tpsettings;sa=updatefrontpage" method="post">
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="tpadmin_form" value="frontpage">
		<div class="cat_bar"><header class="category_header">' . $txt['tp-frontpage_settings'] . '</header></div>
		<p class="description">' , $txt['tp-helpfrontpage'] , '</p>
		<div id="frontpage-settings" class="generic_list_wrapper">
			<div class="content">
				<div class="formtable padding-div">
					<dl class="settings">
						<dt>
							<label for="field_name">', $txt['tp-whattoshow'], '</label>
						</dt>
						<dd>
							<input type="radio" id="tp_front_type1" name="tp_front_type" value="forum_selected" ' , $context['TPortal']['front_type']=='forum_selected' ? 'checked' : '' , '><label for="tp_front_type1"> '.$txt['tp-selectedforum'].'</label><br>
							<input type="radio" id="tp_front_type2" name="tp_front_type" value="forum_selected_articles" ' , $context['TPortal']['front_type']=='forum_selected_articles' ? 'checked' : '' , '><label for="tp_front_type2"> '.$txt['tp-selectbothforum'].'</label><br>
							<input type="radio" id="tp_front_type3" name="tp_front_type" value="forum_only" ' , $context['TPortal']['front_type']=='forum_only' ? 'checked' : '' , '><label for="tp_front_type3"> '.$txt['tp-onlyforum'].'</label><br>
							<input type="radio" id="tp_front_type4" name="tp_front_type" value="forum_articles" ' , $context['TPortal']['front_type']=='forum_articles' ? 'checked' : '' , '><label for="tp_front_type4"> '.$txt['tp-bothforum'].'</label><br>
							<input type="radio" id="tp_front_type5" name="tp_front_type" value="articles_only" ' , $context['TPortal']['front_type']=='articles_only' ? 'checked' : '' , '><label for="tp_front_type5"> '.$txt['tp-onlyarticles'].'</label><br>
							<input type="radio" id="tp_front_type6" name="tp_front_type" value="single_page"  ' , $context['TPortal']['front_type']=='single_page' ? 'checked' : '' , '><label for="tp_front_type6"> '.$txt['tp-singlepage'].'</label><br>
							<input type="radio" id="tp_front_type7" name="tp_front_type" value="frontblock"  ' , $context['TPortal']['front_type']=='frontblock' ? 'checked' : '' , '><label for="tp_front_type7"> '.$txt['tp-frontblocks'].'</label><br>
							<input type="radio" id="tp_front_type8" name="tp_front_type" value="boardindex"  ' , $context['TPortal']['front_type']=='boardindex' ? 'checked' : '' , '><label for="tp_front_type8"> '.$txt['tp-boardindex'].'</label><br>
						</dd>
						<dt>
							<label for="field_name">', $txt['tp-frontblockoption'], '</label>
						</dt>
						<dd>
							<input type="radio" id="tp_frontblock_type1" name="tp_frontblock_type" value="single"  ' , $context['TPortal']['frontblock_type']=='single' ? 'checked' : '' , '><label for="tp_frontblock_type1"> '.$txt['tp-frontblocksingle'].'</label><br>
							<input type="radio" id="tp_frontblock_type2" name="tp_frontblock_type" value="first"  ' , $context['TPortal']['frontblock_type']=='first' ? 'checked' : '' , '><label for="tp_frontblock_type2"> '.$txt['tp-frontblockfirst'].'</label><br>
							<input type="radio" id="tp_frontblock_type3" name="tp_frontblock_type" value="last"  ' , $context['TPortal']['frontblock_type']=='last' ? 'checked' : '' , '><label for="tp_frontblock_type3"> '.$txt['tp-frontblocklast'].'</label><br>
						</dd>
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-frontpageoptionsdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="tp-frontpageoptions">',$txt['tp-frontpageoptions'],'</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_frontpage_visual_left" name="tp_frontpage_visual_left" value="1" ' , $context['TPortal']['frontpage_visualopts_admin']['left']>0 ? 'checked' : '' , '><label for="tp_frontpage_visual_left"> ',$txt['tp-displayleftpanel'],'</label><br>
							<input type="checkbox" id="tp_frontpage_visual_right" name="tp_frontpage_visual_right" value="1" ' , $context['TPortal']['frontpage_visualopts_admin']['right']>0 ? 'checked' : '' , '><label for="tp_frontpage_visual_right"> ',$txt['tp-displayrightpanel'],'</label><br>
							<input type="checkbox" id="tp_frontpage_visual_top" name="tp_frontpage_visual_top" value="1" ' , $context['TPortal']['frontpage_visualopts_admin']['top']>0 ? 'checked' : '' , '><label for="tp_frontpage_visual_top"> ',$txt['tp-displaytoppanel'],'</label><br>
							<input type="checkbox" id="tp_frontpage_visual_center" name="tp_frontpage_visual_center" value="1" ' , $context['TPortal']['frontpage_visualopts_admin']['center']>0 ? 'checked' : '' , '><label for="tp_frontpage_visual_center"> ',$txt['tp-displaycenterpanel'],'</label><br>
							<input type="checkbox" id="tp_frontpage_visual_lower" name="tp_frontpage_visual_lower" value="1" ' , $context['TPortal']['frontpage_visualopts_admin']['lower']>0 ? 'checked' : '' , '><label for="tp_frontpage_visual_lower"> ',$txt['tp-displaylowerpanel'],'</label><br>
							<input type="checkbox" id="tp_frontpage_visual_bottom" name="tp_frontpage_visual_bottom" value="1" ' , $context['TPortal']['frontpage_visualopts_admin']['bottom']>0 ? 'checked' : '' , '><label for="tp_frontpage_visual_bottom"> ',$txt['tp-displaybottompanel'],'</label><br>
						</dd>
					</dl>
					<hr>
					<div class="bbc_strong">', $txt['tp-frontpage_layout'], '</div>
					<div>
						<div class="tpartlayoutfp"><input type="radio" id="tp_frontpage_layout1" name="tp_frontpage_layout" value="1" ' ,
						$context['TPortal']['frontpage_layout']<2 ? 'checked' : '' , '><label for="tp_frontpage_layout1"> A ' ,
						$context['TPortal']['frontpage_layout']<2 ? '' : '' , '
								<br><img style="margin-top:5px" src="' .$settings['tp_images_url']. '/edit_art_cat_a.png"/></label>
						</div>
						<div class="tpartlayoutfp"><input type="radio" id="tp_frontpage_layout2" name="tp_frontpage_layout" value="2" ' ,
						$context['TPortal']['frontpage_layout']==2 ? 'checked' : '' , '><label for="tp_frontpage_layout2"> B ' ,
						$context['TPortal']['frontpage_layout']==2 ? '' : '' , '
							<br><img style="margin-top:5px" src="' .$settings['tp_images_url']. '/edit_art_cat_b.png"/></label>
						</div>
						<div class="tpartlayoutfp"><input type="radio" id="tp_frontpage_layout3" name="tp_frontpage_layout" value="3" ' ,
						$context['TPortal']['frontpage_layout']==3 ? 'checked' : '' , '><label for="tp_frontpage_layout3"> C ' ,
						$context['TPortal']['frontpage_layout']==3 ? '' : '' , '
							<br><img style="margin-top:5px" src="' .$settings['tp_images_url']. '/edit_art_cat_c.png"/></label>
						</div>
						<div class="tpartlayoutfp"><input type="radio" id="tp_frontpage_layout4" name="tp_frontpage_layout" value="4" ' ,
						$context['TPortal']['frontpage_layout']==4 ? 'checked' : '' , '><label for="tp_frontpage_layout4"> D ' ,
						$context['TPortal']['frontpage_layout']==4 ? '' : '' , '
							<br><img style="margin-top:5px" src="' .$settings['tp_images_url']. '/edit_art_cat_d.png"/></label>
						</div>
						<br class="clear" />
					</div>
					<div class="bbc_strong">
						', $txt['tp-articlelayouts'], '
					</div>
					<div>';	foreach($context['TPortal']['admin_layoutboxes'] as $box)
								echo '
									<div class="tpartlayouttype">
										<input type="radio" id="tp_frontpage_catlayout'.$box['value'].'" name="tp_frontpage_catlayout" value="'.$box['value'].'"' , $context['TPortal']['frontpage_catlayout']==$box['value'] ? ' checked="checked"' : '' , '><label for="tp_frontpage_catlayout'.$box['value'].'">
										'.$box['label'].'<br><img style="margin: 4px 4px 4px 10px;" src="' , $settings['tp_images_url'] , '/TPcatlayout'.$box['value'].'.png" alt="tplayout'.$box['value'].'" /></label>
									</div>';

							if(empty($context['TPortal']['frontpage_template']))
								$context['TPortal']['frontpage_template'] = '
<span class="upperframe"><span></span></span>
<div class="roundframe">
	<div class="title_bar">
		<header class="category_header"><span class="left"></span>{article_title} </header>
	</div>
	<div class="flow_hidden">
		<div class="article_info">
			{article_options}
			{article_category}
			{article_date}
			{article_author}
			{article_views}
			{article_rating}
		</div>
		<div class="tp_underline"></div>
		<div class="article_padding">
			{article_text}
			{article_bookmark}
			{article_boardnews}
			{article_moreauthor}
			{article_morelinks}
		</div>
	</div>
</div>
<span class="lowerframe" style="margin-bottom: 5px;"></span>';
							echo '<br class="clear" />
				</div>
				<div>
					<a class="helpicon i-help bbc_strong" href="' . $scripturl . '?action=quickhelp;help=',$txt['reset_custom_template_layoutdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
					', $txt['reset_custom_template_layout'] ,'
					<textarea class="tp_customlayout" name="tp_frontpage_template">' . $context['TPortal']['frontpage_template'] . '</textarea><br>
				</div>
				<hr>
					<dl class="settings">
						<dt>
							<label for="tp_frontpage_limit">', $txt['tp-numberofposts'], '</label>
						</dt>
						<dd>
						  <input type="number" id="tp_frontpage_limit" name="tp_frontpage_limit" value="' ,$context['TPortal']['frontpage_limit'], '" style="width: 6em" min="1" maxlength="5"><br>
						</dd>
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-sortingoptionsdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="tp_frontpage_usorting">',$txt['tp-sortingoptions'],'</label>
						</dt>
						<dd>
							<select name="tp_frontpage_usorting" id="tp_frontpage_usorting">
								<option value="date"' , $context['TPortal']['frontpage_visualopts_admin']['sort']=='date' ? ' selected="selected"' : '' , '>' , $txt['tp-sortoptions1'] , '</option>
								<option value="author_id"' , $context['TPortal']['frontpage_visualopts_admin']['sort']=='author_id' ? ' selected="selected"' : '' , '>' , $txt['tp-sortoptions2'] , '</option>
								<option value="parse"' , $context['TPortal']['frontpage_visualopts_admin']['sort']=='parse' ? ' selected="selected"' : '' , '>' , $txt['tp-sortoptions3'] , '</option>
								<option value="id"' , $context['TPortal']['frontpage_visualopts_admin']['sort']=='id' ? ' selected="selected"' : '' , '>' , $txt['tp-sortoptions4'] , '</option>
							</select>&nbsp;
							<select name="tp_frontpage_sorting_order">
								<option value="desc"' , $context['TPortal']['frontpage_visualopts_admin']['sortorder']=='desc' ? ' selected="selected"' : '' , '>' , $txt['tp-sortdirection1'] , '</option>
								<option value="asc"' , $context['TPortal']['frontpage_visualopts_admin']['sortorder']=='asc' ? ' selected="selected"' : '' , '>' , $txt['tp-sortdirection2'] , '</option>
							</select>
						</dd>
					</dl>
					<hr>
					<dl class="settings">
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-allowguestsdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="tp_allow_guestnews">', $txt['tp-allowguests'], '
						</dt>
						<dd>
							<input type="checkbox" id="tp_allow_guestnews" name="tp_allow_guestnews" value="1" ' , $context['TPortal']['allow_guestnews']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="field_name">', $txt['tp-showforumposts'], '</label>
						</dt>
						<dd>';
		echo '
							<select name="tp_ssiboard" size="5" multiple="multiple" required>
							<option value="0" ' , is_array($context['TPortal']['SSI_boards']) && in_array(0 , $context['TPortal']['SSI_boards']) ? ' selected="selected"' : '' , '>'.$txt['tp-none2'].'</option>';
            if(is_countable($context['TPortal']['boards'])) {
                $tn = count($context['TPortal']['boards']);
            }
            else {
                $tn = 0;
            }

            for($n=0 ; $n<$tn; $n++) {
                echo '
								<option value="'.$context['TPortal']['boards'][$n]['id'].'"' , is_array($context['TPortal']['SSI_boards']) && in_array($context['TPortal']['boards'][$n]['id'] , $context['TPortal']['SSI_boards']) ? ' selected="selected"' : '' , '>'.$context['TPortal']['boards'][$n]['name'].'</option>';
            }

		echo '
							</select><br>
						</dd>
						<dt>
							<label for="tp_frontpage_limit_len">', $txt['tp-lengthofposts'], '</label>
						</dt>
						<dd>
						  <input type="number" id="tp_frontpage_limit_len" name="tp_frontpage_limit_len"value="' ,$context['TPortal']['frontpage_limit_len'], '" style="width: 6em" maxlength="5" ><br>
						</dd>
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-forumposts_avatardesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="tp_forumposts_avatar">', $txt['tp-forumposts_avatar'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_forumposts_avatar" name="tp_forumposts_avatar" value="1" ' , $context['TPortal']['forumposts_avatar']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-useattachmentdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="tp_use_attachment">', $txt['tp-useattachment'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_use_attachment" name="tp_use_attachment" value="1" ' , $context['TPortal']['use_attachment']=='1' ? 'checked' : '' , '><br>
						</dd>
						<dt>
							<label for="tp_boardnews_divheader">'.$txt['tp-boardnews_divheader'].'</label>
						</dt>
						<dd>
						  <select id="tp_boardnews_divheader" name="tp_boardnews_divheader" value="' ,$context['TPortal']['boardnews_divheader'], '" >
								<option value="title_bar"' , $context['TPortal']['boardnews_divheader']=='title_bar' ? ' selected="selected"' : '' , '>title_bar</option>
								<option value="cat_bar"' , $context['TPortal']['boardnews_divheader']=='cat_bar' ? ' selected="selected"' : '' , '>cat_bar</option>
								<option value="tp_half21"' , $context['TPortal']['boardnews_divheader']=='tp_half21' ? ' selected="selected"' : '' , '>tp_half21</option>
							</select>
						</dd>
						<dt>
							<label for="tp_boardnews_headerstyle">'.$txt['tp-boardnews_headerstyle'].'</label>
						</dt>
						<dd>
						  <select id="tp_boardnews_headerstyle" name="tp_boardnews_headerstyle" value="' ,$context['TPortal']['boardnews_headerstyle'], '">
								<option value="category_header"' , $context['TPortal']['boardnews_headerstyle']=='boardnews_cat_header' ? ' selected="selected"' : '' , '>boardnews_cat_header</option>
								<option value="category_header"' , $context['TPortal']['boardnews_headerstyle']=='category_header' ? ' selected="selected"' : '' , '>category_header</option>
							</select>
						</dd>
						<dt>
							<label for="tp_boardnews_divbody">'.$txt['tp-boardnews_divbody'].'</label>
						</dt>
						<dd>
						  <select id="tp_boardnews_divbody" name="tp_boardnews_divbody" value="' ,$context['TPortal']['boardnews_divbody'], '">
								<option value="content"' , $context['TPortal']['boardnews_divbody']=='content' ? ' selected="selected"' : '' , '>content</option>
								<option value="content"' , $context['TPortal']['boardnews_divbody']=='content' ? ' selected="selected"' : '' , '>content+noup</option>
								<option value="roundframe"' , $context['TPortal']['boardnews_divbody']=='roundframe' ? ' selected="selected"' : '' , '>roundframe</option>';
			echo '
						</select>
						</dd>
					</dl>
				</div>
				<div class="submitbutton"><input type="submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
			</div>
		</div>
	</form>';
}
// Article Categories page
function template_categories()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

	echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tparticles;sa=newcategory" method="post" enctype="multipart/form-data" onsubmit="syncTextarea();">
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="tpadmin_form" value="categories">
		<div class="cat_bar">
			<header class="category_header">' . $txt['tp-artcat'] . '</header>
		</div>
		<p class="description">' , $txt['tp-helpcats'] , '</p>
		<div id="edit-category" class="generic_list_wrapper">
			<div class="generic_list">
				<ul class="category_header">
					<li>
						<div class="tp_artcat_name">' , $txt['tp-artcat'] , '</div>
						<div class="tp_artcat_action">' , $txt['tp-actions'] , '</div>
					</li>
				</ul>
				<ul class="mlist">';

		if(isset($context['TPortal']['editcats']) && count($context['TPortal']['editcats'])>0)
		{
			$alt=true;
			foreach($context['TPortal']['editcats'] as $c => $cat)
			{
				echo '
					<li>
						<div class="tp_artcat_name">' , str_repeat("- ",$cat['indent']) , '<a href="' . $scripturl . '?action=admin;area=tparticles;sa=categories;cu='.$cat['id'].'" title="' .$txt['tp-editcategory']. '">' , $cat['name'] , '</a>' , isset($context['TPortal']['cats_count'][$cat['id']]) ? ' (' . ($context['TPortal']['cats_count'][$cat['id']]>1 ? $txt['tp-articles'] : $txt['tp-article']) . ': '.$context['TPortal']['cats_count'][$cat['id']].')' : '' , '</div>
						<div class="tp_artcat_action">
							<a href="' . $scripturl . '?cat=' . $cat['id'] . '" title="' . $txt['tp-viewcategory'] . '"><img src="' . $settings['tp_images_url'] . '/TPfilter.png" alt="" /></a>&nbsp;
							<a href="' . $scripturl . '?action=admin;area=tparticles;sa=categories;cu='.$cat['id'].'" title="' .$txt['tp-editcategory']. '"><img src="' . $settings['tp_images_url'] . '/TPconfig_sm.png" alt="" /></a>&nbsp;
							<a href="' . $scripturl . '?action=admin;area=tparticles;sa=newcategory;child;cu=' . $cat['id'] . '" title="' . $txt['tp-addsubcategory'] . '"><img src="' . $settings['tp_images_url'] . '/TPadd.png" alt="" /></a>&nbsp;
							<a href="' . $scripturl . '?action=admin;area=tparticles;sa=newcategory;copy;cu=' . $cat['id'] . '" title="' . $txt['tp-copycategory'] . '"><img src="' . $settings['tp_images_url'] . '/TPcopy.png" alt="" /></a>&nbsp;
							<a href="' . $scripturl . '?action=admin;area=tparticles;sa=delcategory;id='.$cat['id'].';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="javascript:return confirm(\''.$txt['tp-confirmcat1'].'  \n'.$txt['tp-confirmcat2'].'\')" title="' . $txt['tp-delete'] . '"><img src="' . $settings['tp_images_url'] . '/TPdelete2.png" alt="" /></a>
						</div>
					</li>';
				$alt = !$alt;
			}
		}
		echo '
				</ul>
			</div>
		</div>
	</form>';
}

// Edit Article Category Page
function template_editcategory()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

		$mg = $context['TPortal']['editcategory'];
		echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tparticles;sa=editcategory" method="post">
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="tpadmin_form" value="editcategory">
		<input type="hidden" name="tpadmin_form_id" value="' . $mg['id'] . '">
		<div class="cat_bar"><header class="category_header">' . $txt['tp-editcategory'] . ' ' ,html_entity_decode($mg['display_name']), '&nbsp;-&nbsp;<a href="'.$scripturl.'?cat='.$mg['id'].'">['.$txt['tp-viewcategory'].']</a></header></div>
		<div id="edit-art-category" class="generic_list_wrapper">
			<div class="content">
				<div class="formtable padding-div">
					<dl class="settings tptitle">
						<dt>
							<b><label for="tp_category_display_name">', $txt['tp-name'], '</label></b>
						</dt>
						<dd>
							<input type="text" id="tp_category_display_name" style="max-width:97%;" name="tp_category_display_name" value="' ,html_entity_decode($mg['display_name']), '" size="50" required>
						</dd>
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-shortnamedesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="tp_category_short_name"><b>', $txt['tp-shortname'], '</b></label>
						</dt>
						<dd>
							<input type="text" id="tp_category_short_name" name="tp_category_short_name" value="' , isset($mg['short_name']) ? $mg['short_name'] : '' , '" size="20"><br>
						</dd>
						<dt>
							<label for="tp_category_parent">', $txt['tp-parent'], '</label>
						</dt>
						<dd>
							<select name="tp_category_parent" id="tp_category_parent" style="max-width:100%;">
								<option value="0"' , $mg['parent']==0 || $mg['parent']=='9999' ? ' selected="selected"' : '' , '>' , $txt['tp-noname'] , '</option>';
				foreach($context['TPortal']['editcats'] as $b => $parent) {
					if($parent['id']!= $mg['id'])
						echo '
								<option value="' . $parent['id'] . '"' , $parent['id']==$mg['parent'] ? ' selected="selected"' : '' , '>' , str_repeat("-",$parent['indent']) ,' ' , html_entity_decode($parent['name']) , '</option>';
				}
					echo '
							</select>
						</dd>
						<dt>
							<label for="tp_category_sort">', $txt['tp-sorting'], '</label>
						</dt>
						<dd>
							<select name="tp_category_sort" id="tp_category_sort">
								<option value="date"' , isset($mg['sort']) && $mg['sort']=='date' ? ' selected="selected"' : '' , '>' , $txt['tp-sortoptions1'] , '</option>
								<option value="author_id"' , isset($mg['sort']) && $mg['sort']=='author_id' ? ' selected="selected"' : '' , '>' , $txt['tp-sortoptions2'] , '</option>
								<option value="parse"' , isset($mg['sort']) && $mg['sort']=='parse' ? ' selected="selected"' : '' , '>' , $txt['tp-sortoptions3'] , '</option>
								<option value="id"' , isset($mg['sort']) && $mg['sort']=='id' ? ' selected="selected"' : '' , '>' , $txt['tp-sortoptions4'] , '</option>
							</select>
							<select name="tp_category_sortorder">
								<option value="desc"' , isset($mg['sortorder']) && $mg['sortorder']=='desc' ? ' selected="selected"' : '' , '>' , $txt['tp-sortdirection1'] , '</option>
								<option value="asc"' , isset($mg['sortorder']) && $mg['sortorder']=='asc' ? ' selected="selected"' : '' , '>' , $txt['tp-sortdirection2'] , '</option>
							</select>
						</dd>
						<dt>
							<label for="tp_category_articlecount">', $txt['tp-articlecount'], '</label>
						</dt>
						<dd>
							<input type="number" id="tp_category_articlecount" name="tp_category_articlecount" value="' , empty($mg['articlecount']) ? $context['TPortal']['frontpage_limit'] : $mg['articlecount']  , '" style="width: 6em">
						</dd>
					</dl>
					<div class="submitbutton"><input type="submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
					<hr>
					<div>
						<div class="bbc_strong">', $txt['tp-catlayouts'], '</div>

						<div class="tpartlayoutfp"><input type="radio" id="tp_category_layout1" name="tp_category_layout" value="1" ' ,
							$mg['layout']==1 ? 'checked' : '' , '> A ' ,
							$mg['layout']==1 ? '' : '' , '
							<div class="tborder" style="margin-top: 5px;">
								 <label for="tp_category_layout1"><img src="' .$settings['tp_images_url']. '/edit_art_cat_a.png"/></label>
							</div>
						</div>
						<div class="tpartlayoutfp"><input type="radio" id="tp_category_layout2" name="tp_category_layout" value="2" ' ,
							$mg['layout']==2 ? 'checked' : '' , '> B ' ,
							$mg['layout']==2 ? '' : '' , '
							<div class="tborder" style="margin-top: 5px;">
								<label for="tp_category_layout2"><img src="' .$settings['tp_images_url']. '/edit_art_cat_b.png"/></label>
							</div>
						</div>
						<div class="tpartlayoutfp"><input type="radio" id="tp_category_layout3" name="tp_category_layout" value="3" ' ,
							$mg['layout']==3 ? 'checked' : '' , '> C ' ,
							$mg['layout']==3 ? '' : '' , '
							<div class="tborder" style="margin-top: 5px;">
								<label for="tp_category_layout3"><img src="' .$settings['tp_images_url']. '/edit_art_cat_c.png"/></label>
							</div>
						</div>
						<div class="tpartlayoutfp"><input type="radio" id="tp_category_layout4" name="tp_category_layout" value="4" ' ,
							$mg['layout']==4 ? 'checked' : '' , '> D ' ,
							$mg['layout']==4 ? '' : '' , '
							<div class="tborder" style="margin-top: 5px;">
								<label for="tp_category_layout4"><img src="' .$settings['tp_images_url']. '/edit_art_cat_d.png"/></label>
							</div>
						</div>
					</div>
					<div class="bbc_strong">'.$txt['tp-articlelayouts']. ':<div>';
			foreach($context['TPortal']['admin_layoutboxes'] as $box)
				echo '
					<div class="tpartlayouttype">
						<input type="radio" id="tp_category_catlayout'.$box['value'].'" name="tp_category_catlayout" value="'.$box['value'].'"' , $mg['catlayout']==$box['value'] ? ' checked="checked"' : '' , '>
						<label for="tp_category_catlayout'.$box['value'].'">'.$box['label'].'<br><img style="margin: 4px 4px 4px 10px;" src="' , $settings['tp_images_url'] , '/TPcatlayout'.$box['value'].'.png" alt="tplayout'.$box['value'].'" /></label>
					</div>';
				if(empty($mg['custom_template']))
					$mg['custom_template'] = '
					<div class="tparticle">
						<div class="cat_bar">
							<header class="category_header"><span class="left"></span>{article_title}</header>
						</div>
						<div class="content">
							<span class="topslice"><span></span></span>
							<div class="article_info">
								{article_avatar}
								{article_options}
								{article_category}
								{article_date}
								{article_author}
								{article_views}
								{article_rating}
							</div>
							<div class="tp_underline"></div>
							<div class="article_padding">{article_text}</div>
							<div class="article_padding">{article_moreauthor}</div>
							<div class="article_padding">{article_morelinks}</div>
							<div class="article_padding">{article_comments}</div>
							<span class="botslice"><span></span></span>
						</div>
					</div>';
				echo '	</div>
						<br class="clear" />
						<h4><a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['reset_custom_template_layoutdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
						', $txt['reset_custom_template_layout'] ,'</h4>
						<textarea class="tp_customlayout" name="tp_category_custom_template">' . $mg['custom_template'] . '</textarea><br>
					</div>
					<hr>
					<dl class="tptitle settings">
						<dt>
							<label for="field_name">', $txt['tp-showchilds'], '</label>
						</dt>
						<dd>
							<input type="radio" name="tp_category_showchild" value="1"' , (isset($mg['showchild']) && $mg['showchild']==1) ? ' checked="checked"' : '' , '> ' , $txt['tp-yes'] , '
							<input type="radio" name="tp_category_showchild" value="0"' , ((isset($mg['showchild']) && $mg['showchild']==0) || !isset($mg['showchild'])) ? ' checked="checked"' : '' , '> ' , $txt['tp-no'] , '<br>
						</dd>
						<dt class="bbc_strong">
							', $txt['tp-allpanels'], '
						</dt>
						<dt>
							<label for="tp_category_leftpanel">', $txt['tp-displayleftpanel'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_category_leftpanel" name="tp_category_leftpanel" value="1"' , !empty($mg['leftpanel']) ? ' checked="checked"' : '' ,' />
						</dd>
						<dt>
							<label for="tp_category_rightpanel">', $txt['tp-displayrightpanel'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_category_rightpanel" name="tp_category_rightpanel" value="1"' , !empty($mg['rightpanel']) ? ' checked="checked"' : '' ,' />
						</dd>
						<dt>
							<label for="tp_category_toppanel">', $txt['tp-displaytoppanel'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_category_toppanel" name="tp_category_toppanel" value="1"' , !empty($mg['toppanel']) ? ' checked="checked"' : '' ,' />
						</dd>
						<dt>
							<label for="tp_category_centerpanel">', $txt['tp-displaycenterpanel'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_category_centerpanel" name="tp_category_centerpanel" value="1"' , !empty($mg['centerpanel']) ? ' checked="checked"' : '' ,' />
						</dd>
						<dt>
							<label for="tp_category_lowerpanel">', $txt['tp-displaylowerpanel'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_category_lowerpanel" name="tp_category_lowerpanel" value="1"' , !empty($mg['lowerpanel']) ? ' checked="checked"' : '' ,' />
						</dd>
						<dt>
							<label for="tp_category_bottompanel">', $txt['tp-displaybottompanel'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_category_bottompanel" name="tp_category_bottompanel" value="1"' , !empty($mg['bottompanel']) ? ' checked="checked"' : '' ,' />
						</dd>
					</dl>
					<dl class="tptitle settings">
						<dt>
							<span class="font-strong">'.$txt['tp-allowedgroups']. '</span>
						</dt>
						<dd>
							<div class="tp_largelist2">';
			// loop through and set membergroups
			$tg=explode(',',$mg['access']);
			foreach($context['TPmembergroups'] as $g) {
				if($g['posts']=='-1' && $g['id']!='1') {
					echo '<input name="tp_category_group_'.$g['id'].'" id="'.$g['name'].'" type="checkbox" value="'.$mg['id'].'"';
					if(in_array($g['id'],$tg))
						echo ' checked';
					echo '><label for="'.$g['name'].'"> '.$g['name'].' </label><br>';
				}
			}
			// if none is chosen, have a control value
				echo '
							</div>
							<input type="checkbox" id="tp_catgroup-2" onclick="invertAll(this, this.form, \'tp_category_group\');" /><label for="tp_catgroup-2"> '.$txt['tp-checkall'].'</label>
							<input type="hidden" name="tp_catgroup-2" value="'.$mg['id'].'">
						</dd>
					</dl>
				</div>
				<div class="submitbutton"><input type="submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
			</div>
		</div>
	</form>';
}

// Add category Page
function template_newcategory()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

	if(isset($_GET['cu']) && is_numeric($_GET['cu']))
		$currcat = $_GET['cu'];

	echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tparticles;sa=addcategory" method="post">
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="tpadmin_form" value="addcategory">
		<div class="cat_bar"><header class="category_header">' . $txt['tp-addcategory'] . '</header></div>
		<p class="description">' , $txt['tp-helpaddcategory'] , '</p>
		<div id="new-category" class="generic_list_wrapper">
			<div class="content ">
				<div class="formtable padding-div">
					<dl class="settings tptitle">
						<dt>
							<b><label for="tp_cat_name">'.$txt['tp-name'].'</label></b>
						</dt>
						<dd>
							<input type="text" id="tp_cat_name" style="max-width:97%;" name="tp_cat_name" value="" size="50" required>
						</dd>
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-shortnamedesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<b><label for="tp_cat_shortname">', $txt['tp-shortname'], '</label></b>
						</dt>
						<dd>
							<input type="text" id="tp_cat_shortname" name="tp_cat_shortname" value="" size="20"><br>
						</dd>
						<dt>
							<label for="tp_cat_parent">'.$txt['tp-parent'].'</label>
						</dt>
						<dd>
							<select size="1" name="tp_cat_parent" id="tp_cat_parent">
								<option value="0">'.$txt['tp-none2'].'</option>';
			if(isset($context['TPortal']['editcats'])){
				foreach($context['TPortal']['editcats'] as $s => $submg ){
						echo '
							<option value="'.$submg['id'].'"' , isset($currcat) && $submg['id']==$currcat ? ' selected="selected"' : '' , '>'. str_repeat("-",$submg['indent']) .' '.$submg['name'].'</option>';
				}
			}
			echo '
							</select><input type="hidden" name="newcategory" value="1">
						<dd>
					</dl>
				</div>
				<div class="submitbutton"><input type="submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
			</div>
		</div>
	</form>';
}

// Category List Page
function template_clist()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

		echo '
	<form  accept-charset="', 'UTF-8', '" name="TPadmin" action="' . $scripturl . '?action=admin;area=tparticles;sa=editclist" method="post">
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="tpadmin_form" value="clist">
		<div class="cat_bar"><header class="category_header">'.$txt['tp-tabs11'].'</header></div>
		<div id="clist" class="admin-area">
			<div class="content">
				<div class="padding-div bbc_strong">'.$txt['tp-clist'].'</div>
				<div class="padding-div">';

		$clist = explode(',',$context['TPortal']['cat_list']);
		echo '
					<input type="hidden" name="tp_clist-1" value="-1">';
		foreach($context['TPortal']['catnames'] as $ta => $val){
			echo '
					<input type="checkbox" name="tp_clist'.$ta.'" value="'.$ta.'"';
			if(in_array($ta, $clist))
				echo ' checked';
			echo '>  '.html_entity_decode($val).'<br>';
		}
		echo '
					<br><input type="checkbox" onclick="invertAll(this, this.form, \'tp_clist\');" />  '.$txt['tp-checkall'].'
				</div>
				<div class="submitbutton"><input type="submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
			</div>
		</div>
	</form>';
}

// Articles page
function template_articles()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

	echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tparticles" method="post" enctype="multipart/form-data" onsubmit="syncTextarea();">
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="tpadmin_form" value="articles">
		<div class="cat_bar">
			<header class="category_header">' , $txt['tp-articles'] , !empty($context['TPortal']['categoryNAME']) ? $txt['tp-incategory']. ' ' . $context['TPortal']['categoryNAME'].' ' : '' ,  '</header>
		</div>
		<p class="description">' , empty($context['TPortal']['categoryNAME']) ? $txt['tp-helparticles'] : $txt['tp-helparticles2'] , '</p>
		<div id="edit-articles" class="generic_list_wrapper">
			<div class="generic_list">';

	if(isset($context['TPortal']['cats']) && count($context['TPortal']['cats'])>0)
	{
		echo '
				<ul class="category_header">
					<li>
						<div class="tp_artcat_name">' , $txt['tp-artcat'] , '</div>
						<div class="tp_artcat_pos">' , $txt['tp-articles'] , '</div>
						<div class="tp_artcat_action">' , $txt['tp-actions'] , '</div>
					</li>
				</ul>
				<ul class="mlist">';
		$alt=true;
		foreach($context['TPortal']['cats'] as $c => $cat)
		{
			echo '
					<li>
						<div class="tp_artcat_name">' , (!empty($cat['indent']) ? str_repeat("- ",$cat['indent']) : '') , '<a href="' . $scripturl . '?action=admin;area=tparticles;sa=articles;cu='.$cat['id'].'" title="' .$txt['tp-articleoptions12']. '">' , $cat['name'] , '</a></div>
						<div class="tp_artcat_pos">' , isset($context['TPortal']['cats_count'][$cat['id']]) ? $context['TPortal']['cats_count'][$cat['id']] : '0' , '</div>
						<div class="tp_artcat_action"><a href="' . $scripturl . '?cat=' . $cat['id'] . '" title="' .$txt['tp-viewcategory']. '"><img src="' . $settings['tp_images_url'] . '/TPfilter.png" alt="" /></a>&nbsp;<a href="' . $scripturl . '?action=admin;area=tparticles;sa=categories;cu=' . $cat['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" title="' .$txt['tp-editcategory']. '"><img src="' . $settings['tp_images_url'] . '/TPconfig_sm.png" alt="" /></a></div>
					</li>';
			$alt = !$alt;
		}
		echo '
				</ul>
			</div>
		</div>';
	}

	// Articles in category Page
	if(isset($context['TPortal']['arts']))
	{
		echo '
				<ul class="category_header">
					<li>
						<div class="tp_artcat_pos">' , $context['TPortal']['sort']=='parse' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-position'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-position'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=articles;cu='.$context['TPortal']['categoryID'].';sort=parse">' , $txt['tp-pos'] , '</a></div>
						<div class="tp_artcat_name">' , $context['TPortal']['sort']=='subject' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-subject'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-subject'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=articles;cu='.$context['TPortal']['categoryID'].';sort=subject">' , $txt['tp-arttitle'] , '</a></div>
						<div class="tp_artcat_author"> ' , $context['TPortal']['sort']=='author_id' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-author'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-author'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=articles;cu='.$context['TPortal']['categoryID'].';sort=author_id">' , $txt['tp-author'] , '</a></div>
						<div class="tp_artcat_date"> ' , $context['TPortal']['sort']=='date' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-date'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-date'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=articles;cu='.$context['TPortal']['categoryID'].';sort=date">' , $txt['tp-date'] , '</a></div>
						<div class="tp_artcat_action">
							' , $context['TPortal']['sort']=='off' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-active'].'" /> ' : '' , '<a title="'.$txt['tp-sort-on-active'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=articles;cu='.$context['TPortal']['categoryID'].';sort=off"><img src="' . $settings['tp_images_url'] . '/TPactive2.png" alt="" /></a>
							' , $context['TPortal']['sort']=='sticky' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-sticky'].'" /> ' : '' , '<a title="'.$txt['tp-sort-on-sticky'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=articles;cu='.$context['TPortal']['categoryID'].';sort=sticky"><img src="' . $settings['tp_images_url'] . '/TPsticky1.png" alt="" /></a>
							' , $context['TPortal']['sort']=='locked' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-locked'].'" /> ' : '' , '<a title="'.$txt['tp-sort-on-locked'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=articles;cu='.$context['TPortal']['categoryID'].';sort=locked"><img src="' . $settings['tp_images_url'] . '/TPlock1.png" alt="" /></a>
							' , $context['TPortal']['sort']=='frontpage' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-frontpage'].'" /> ' : '' , '<a title="'.$txt['tp-sort-on-frontpage'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=articles;cu='.$context['TPortal']['categoryID'].';sort=frontpage"><img src="' . $settings['tp_images_url'] . '/TPfront.png" alt="*" /></a>
						</div>
						<div class="tp_artcat_type"> ' , $context['TPortal']['sort']=='type' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-type'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-type'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=articles;cu='.$context['TPortal']['categoryID'].';sort=type">' , $txt['tp-type'] , '</a></div>
						<div class="tp_artcat_delete bbc_strong">'.$txt['tp-delete'].'</div>
					</li>
				</ul>
				<ul class="mlist">';

		foreach($context['TPortal']['arts'] as $a => $alink)
		{
			$alink['pos'] = $alink['pos']=='' ? 0 : $alink['pos'];
			$catty = $alink['category'];

			echo '
					<li>
						<div class="tp_artcat_pos"><a name="article'.$alink['id'].'"></a><input type="number" value="'.$alink['pos'].'" name="tp_article_pos'.$alink['id'].'" style="width: 5em" /></div>
						<div class="tp_artcat_name">' , $alink['locked']==0 ? '<a href="' . $scripturl . '?action=admin;area=tparticles;sa=editarticle;article=' . $alink['id'] . '">' . $alink['subject'].'</a>' : '<img title="'.$txt['tp-islocked'].'" src="' .$settings['tp_images_url']. '/TPlock1.png" alt="'.$txt['tp-islocked'].'"  />&nbsp;' . $alink['subject'] , '</div>
						<div class="tp_artcat_author"><a href="' . $scripturl . '?action=profile;u=' , $alink['author_id'], '">'.$alink['author'] .'</a></div>
						<div class="tp_artcat_date">' , standardTime($alink['date']) , '</div>
						<div class="tp_artcat_action">
							<img style="cursor: pointer;" class="toggleActive" id="artActive' .$alink['id']. '" title="'.$txt['tp-activate'].'" src="' .$settings['tp_images_url']. '/TPactive' , $alink['off']=='1' ? '1' : '2' , '.png" alt="'.$txt['tp-activate'].'"  />
							<a href="',$scripturl, '?page=',$alink['id'],'"><img title="'.$txt['tp-preview'].'" src="' .$settings['tp_images_url']. '/TPfilter.png" alt="" /></a>
							' , $alink['locked']==0 ?
							'<a href="' . $scripturl . '?action=admin;area=tparticles;sa=editarticle;article='.$alink['id']. '"><img title="'.$txt['tp-edit'].'" src="' .$settings['tp_images_url']. '/TPconfig_sm.png" alt="'.$txt['tp-edit'].'"  /></a>' : '<img title="'.$txt['tp-islocked'].'" src="' .$settings['tp_images_url']. '/TPconfig_sm2.png" alt="'.$txt['tp-islocked'].'"  />' , '
							<img style="cursor: pointer;" class="toggleSticky" id="artSticky' .$alink['id']. '" title="'.$txt['tp-setsticky'].'" src="' .$settings['tp_images_url']. '/TPsticky' , $alink['sticky']=='1' ? '1' : '2' , '.png" alt="'.$txt['tp-setsticky'].'"  />
							<img style="cursor: pointer;" class="toggleLock" id="artLock' .$alink['id']. '" title="'.$txt['tp-setlock'].'" src="' .$settings['tp_images_url']. '/TPlock' , $alink['locked']=='1' ? '1' : '2' , '.png" alt="'.$txt['tp-setlock'].'"  />
							<img style="cursor: pointer;" class="toggleFront" id="artFront' .$alink['id']. '" title="'.$txt['tp-setfrontpage'].'" src="' .$settings['tp_images_url']. '/TPfront' , $alink['frontpage']=='1' ? '' : '2' , '.png" alt="'.$txt['tp-setfrontpage'].'"  />
							<img style="cursor: pointer;" class="toggleFeatured" id="artFeatured' .$alink['id']. '" title="'.$txt['tp-featured'].'" src="' .$settings['tp_images_url']. '/TPflag' , $alink['featured']=='1' ? '' : '2' , '.png" alt="'.$txt['tp-featured'].'"  />
						</div>
						<div class="tp_artcat_type">' , empty($alink['type']) ? 'html' : $alink['type'] , '</div>
						<div class="tp_artcat_delete"><a href="' . $scripturl . '?action=admin;area=tparticles;' . $context['session_var'] . '=' . $context['session_id'].';artdelete=' .$alink['id'] , !empty($_GET['cu']) ? ';cu=' . $_GET['cu'] : '' , '" onclick="javascript:return confirm(\''.$txt['tp-articleconfirmdelete'].'\')"><img title="'.$txt['tp-delete'].'" src="' .$settings['tp_images_url']. '/TPdelete2.png" alt="'.$txt['tp-delete'].'"  /></a></div>
					</li>';
			}
		echo '
				</ul>';

		if( !empty($context['TPortal']['pageindex']))
			echo '
				<div class="middletext padding-div">
					'.$context['TPortal']['pageindex'].'
				</div>';
		echo '
				<div class="submitbutton"><input type="submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'">
					<input type="hidden" name="tpadmin_form_category" value="' . $catty . '">
				</div>';
	}
	else
		echo '
				';

		echo '
			</div>
		</div>
	</form>';
}

// Uncategorized articles Page
function template_strays()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

	echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tparticles;sa=strays" method="post" enctype="multipart/form-data" onsubmit="syncTextarea();">
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="tpadmin_form" value="strays">
		<div class="cat_bar"><header class="category_header">' . $txt['tp-uncategorised2'] . '</header></div>
		<p class="information smalltext">' , $txt['tp-helpstrays'] , '</p>
		<div id="uncategorized" class="generic_list_wrapper">';

	if(isset($context['TPortal']['arts_nocat'])) {
		echo '
			<div class="content">
				<div>
					<table class="table_grid tp_grid" style="width:100%">
					<thead>
						<tr class="title_bar category_header">
						<th scope="col">
							<div>
								<div style="width:7%;" class="pos float-items bbc_strong">'.$txt['tp-select'].'</div>
								<div style="width:25%;" class="name float-items">' , $context['TPortal']['sort']=='subject' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-subject'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-subject'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=strays;sort=subject">' , $txt['tp-arttitle'] , '</a></div>
								<div style="width:10%;" class="title-admin-area float-items"> ' , $context['TPortal']['sort']=='author_id' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-author'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-author'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=strays;sort=author_id">' , $txt['tp-author'] , '</a></div>
								<div style="width:18%;" class="title-admin-area float-items"> ' , $context['TPortal']['sort']=='date' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-date'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-date'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=strays;sort=date">' , $txt['tp-date'] , '</a></div>
								<div style="width:27%;" class="title-admin-area float-items"></div>
								<div style="width:10%;" class="title-admin-area float-items"> ' , $context['TPortal']['sort']=='type' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-type'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-type'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=strays;sort=type">' , $txt['tp-type'] , '</a></div>
							</div>
						</th>
						</tr>
					</thead>
					<tbody>';

		foreach($context['TPortal']['arts_nocat'] as $a => $alink) {
			$alink['pos'] = $alink['pos']=='' ? 0 : $alink['pos'];
			$catty = $alink['category'];

			echo '
						<tr class="content">
						<td class="articles">
							<div>
							<div style="width:7%;" class="adm-pos float-items">
									<div class="smalltext float-items tpcenter">
										<input type="checkbox" name="tp_article_stray'.$alink['id'].'" value="1"  />&nbsp;&nbsp;
									</div>
								</div>
								<div style="width:25%;" class="adm-name float-items">
									' , $alink['locked']==0 ? '<a href="' . $scripturl . '?action=admin;area=tparticles;sa=editarticle;article=' . $alink['id'] . '">' . (!empty($alink['subject']) ? $alink['subject'] : $txt['tp-noname']) . '</a>' : '<img title="'.$txt['tp-islocked'].'" src="' .$settings['tp_images_url']. '/TPlock1.png" alt="'.$txt['tp-islocked'].'"  />&nbsp;' . (!empty($alink['subject']) ? $alink['subject'] : $txt['tp-noname']) , '
								</div>
								<a href="" class="clickme">'.$txt['tp-more'].'</a>
								<div class="box floatleft" style="width:68%;">
									<div style="width:14.8%;" class="smalltext fullwidth-on-res-layout float-items">
										<div class="show-on-responsive">
											' , $context['TPortal']['sort']=='author_id' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-author'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-author'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=strays;sort=author_id">' , $txt['tp-author'] , '</a>
										</div>
										<div class="size-on-responsive">
											<a href="' . $scripturl . '?action=profile;u=' , $alink['author_id'], '">'.$alink['author'] .'</a>
										</div>
									</div>
									<div style="width:29.8%;" class="smalltext fullwidth-on-res-layout float-items">
										<div class="show-on-responsive">
											' , $context['TPortal']['sort']=='date' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-date'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-date'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=strays;sort=date">' , $txt['tp-date'] , '</a>
										</div>
										<div class="size-on-responsive">' , standardTime($alink['date']) , '</div>
									</div>
									<div style="width:36%;" class="smalltext fullwidth-on-res-layout float-items">
										<div class="show-on-responsive bbc_strong" style="margin-top:0.5%;">'.$txt['tp-editarticleoptions2'].'></div>
										<div class="size-on-responsive">
											<img style="cursor: pointer;" class="toggleActive" id="artActive' .$alink['id']. '" title="'.$txt['tp-activate'].'" src="' .$settings['tp_images_url']. '/TPactive' , $alink['off']=='1' ? '1' : '2' , '.png" alt="'.$txt['tp-activate'].'"  />
											<a href="',$scripturl, '?page=',$alink['id'],'"><img title="'.$txt['tp-preview'].'" src="' .$settings['tp_images_url']. '/TPfilter.png" alt="" /></a>
											' , $alink['locked']==0 ?
											'<a href="' . $scripturl . '?action=admin;area=tparticles;sa=editarticle;article='.$alink['id']. '"><img title="'.$txt['tp-edit'].'" src="' .$settings['tp_images_url']. '/TPconfig_sm.png" alt="'.$txt['tp-edit'].'"  /></a>' : '<img title="'.$txt['tp-islocked'].'" src="' .$settings['tp_images_url']. '/TPconfig_sm2.png" alt="'.$txt['tp-islocked'].'"  />' , '
											<img style="cursor: pointer;" class="toggleSticky" id="artSticky' .$alink['id']. '" title="'.$txt['tp-setsticky'].'" src="' .$settings['tp_images_url']. '/TPsticky' , $alink['sticky']=='1' ? '1' : '2' , '.png" alt="'.$txt['tp-setsticky'].'"  />
											<img style="cursor: pointer;" class="toggleLock" id="artLock' .$alink['id']. '" title="'.$txt['tp-setlock'].'" src="' .$settings['tp_images_url']. '/TPlock' , $alink['locked']=='1' ? '1' : '2' , '.png" alt="'.$txt['tp-setlock'].'"  />
											<img style="cursor: pointer;" class="toggleFront" id="artFront' .$alink['id']. '" title="'.$txt['tp-setfrontpage'].'" src="' .$settings['tp_images_url']. '/TPfront' , $alink['frontpage']=='1' ? '' : '2' , '.png" alt="'.$txt['tp-setfrontpage'].'"  />											<img style="cursor: pointer;" class="toggleFeatured" id="artFeatured' .$alink['id']. '" title="'.$txt['tp-featured'].'" src="' .$settings['tp_images_url']. '/TPflag' , $alink['featured']=='1' ? '' : '2' , '.png" alt="'.$txt['tp-featured'].'"  />
										</div>
									</div>
									<div style="width:10%" class="smalltext fullwidth-on-res-layout float-items tpcenter" >
										<div class="show-on-responsive">
										' , $context['TPortal']['sort']=='type' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-type'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-type'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=strays;sort=type">' , $txt['tp-type'] , '</a>
										</div>
										<div style="text-transform:uppercase;">' , empty($alink['type']) ? 'html' : $alink['type'] , '</div>
									</div>
									<div style="width:6%" class="smalltext fullwidth-on-res-layout float-items tpcenter">
										<div class="show-on-responsive bbc_strong">'.$txt['tp-delete'].'</div>
										<a href="' . $scripturl . '?action=admin;area=tparticles;cu=-1;' . $context['session_var'] . '=' . $context['session_id'].';artdelete=' .$alink['id']. '" onclick="javascript:return confirm(\''.$txt['tp-articleconfirmdelete'].'\')">
										<img title="'.$txt['tp-delete'].'" src="' .$settings['tp_images_url']. '/TPdelete2.png" alt="'.$txt['tp-delete'].'"  /></a>
									</div>
							  </div>
						</div>
						</td>
						</tr>';
		}
			echo '
					</tbody>
					</table>';
			if( !empty($context['TPortal']['pageindex'])) {
				echo '
					<div class="middletext padding-div">
						'.$context['TPortal']['pageindex'].'
					</div>';
            }
			echo '
				</div>';

		if(isset($context['TPortal']['allcats'])) {
			echo '
				<br><div class="padding-div">
				<select name="tp_article_cat">
					<option value="0">' . $txt['tp-createnew'] . '</option>';
			foreach($context['TPortal']['allcats'] as $submg) {
  					echo '
						<option value="'.$submg['id'].'">',  ( isset($submg['indent']) && $submg['indent'] > 1 ) ? str_repeat("-", ($submg['indent']-1)) : '' , ' '. $txt['tp-assignto'] . $submg['name'].'</option>';
            }
			echo '
				</select>
				<input name="tp_article_new" value="" size="40" />
				</div>';
		}
		echo '
				<div class="submitbutton"><input type="submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
			</div>';
	}
	else {
		echo '
			<div class="content">
				<div class="content3"></div>
			</div>';
    }
	echo '
		</div>
	</form>';
}

// Article Submissions Page
function template_submission()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

	echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tparticles;sa=submission" method="post" enctype="multipart/form-data" onsubmit="syncTextarea();">
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="tpadmin_form" value="submission">
		<div class="cat_bar"><header class="category_header">' . $txt['tp-submissionsettings']  . '</header></div>
		<p class="description">' , $txt['tp-helpsubmissions'] , '</p>
		<div id="submissions" class="generic_list_wrapper">
			<div class="content">';

	if(isset($context['TPortal']['arts_submissions']))
	{
		echo '
				<table class="table_grid tp_grid" style="width:100%">
					<thead>
						<tr class="title_bar category_header">
						<th scope="col" class="articles">
							<div class="category_header3">
								<div style="width:7%;" class="pos float-items bbc_strong">'.$txt['tp-select'].'</div>
								<div style="width:25%;" class="name float-items">' , $context['TPortal']['sort']=='subject' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_up.png" alt="'.$txt['tp-sort-on-subject'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-subject'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=submission;sort=subject">' , $txt['tp-arttitle'] , '</a></div>
								<div style="width:10%;" class="title-admin-area float-items"> ' , $context['TPortal']['sort']=='author_id' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_up.png" alt="'.$txt['tp-sort-on-author'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-author'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=submission;sort=author_id">' , $txt['tp-author'] , '</a></div>
								<div style="width:20%;" class="title-admin-area float-items"> ' , $context['TPortal']['sort']=='date' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-date'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-date'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=submission;sort=date">' , $txt['tp-date'] , '</a></div>
								<div style="width:25%;" class="title-admin-area float-items bbc_strong">&nbsp;</div>
								<div style="width:13%;" class="title-admin-area float-items"> ' , $context['TPortal']['sort']=='type' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_up.png" alt="'.$txt['tp-sort-on-type'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-type'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=submission;sort=type">' , $txt['tp-type'] , '</a></div>
							</div>
						</th>
						</tr>
					</thead>
					<tbody>';

		foreach($context['TPortal']['arts_submissions'] as $a => $alink)
		{
			$alink['pos'] = $alink['pos']=='' ? 0 : $alink['pos'];
			$catty = $alink['category'];

			echo '
						<tr class="content">
						<td class="articles">
							<div>
								<div style="width:7%;" class="adm-pos float-items">
									<input type="checkbox" name="tp_article_submission'.$alink['id'].'" value="1"  />
								</div>
								<div style="width:25%;" class="adm-name float-items">
									' , $alink['locked']==0 ? '<a href="' . $scripturl . '?action=admin;area=tparticles;sa=editarticle;article=' . $alink['id'] . '"> ' . (!empty($alink['subject']) ? $alink['subject'] : $txt['tp-noname']) . '</a>' : '<img title="'.$txt['tp-islocked'].'" src="' .$settings['tp_images_url']. '/TPlock1.png" alt="'.$txt['tp-islocked'].'"  />&nbsp;' . (!empty($alink['subject']) ? $alink['subject'] : $txt['tp-noname']) , '
								</div>
								<a href="" class="clickme">'.$txt['tp-more'].'</a>
								<div class="box floatleft" style="width:68%;">
									<div style="width:14.8%;" class="smalltext fullwidth-on-res-layout float-items">
										<div class="show-on-responsive"> ' , $context['TPortal']['sort']=='author_id' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-author'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-author'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=submission;sort=author_id">' , $txt['tp-author'] , '</a></div>
										<div class="size-on-responsive"><a href="' . $scripturl . '?action=profile;u=' , $alink['author_id'], '">'.$alink['author'] .'</a></div>
									</div>
									<div style="width:29.8%;" class="smalltext fullwidth-on-res-layout float-items">
										<div class="show-on-responsive"> ' , $context['TPortal']['sort']=='date' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-date'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-date'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=submission;sort=date">' , $txt['tp-date'] , '</a></div>
										<div class="size-on-responsive">' , standardTime($alink['date']) , '</div>
									</div>
									<div style="text-align:left;width:37.5%;" class="smalltext fullwidth-on-res-layout float-items">
										<div class="show-on-responsive bbc_strong" style="margin-top:0.5%;">'.$txt['tp-editarticleoptions2'].'</div>
										<div class="size-on-responsive">
										<img style="cursor: pointer;" class="toggleActive" id="artActive' .$alink['id']. '" title="'.$txt['tp-activate'].'" src="' .$settings['tp_images_url']. '/TPactive' , $alink['off']=='1' ? '1' : '2' , '.png" alt="'.$txt['tp-activate'].'"  />
										<a href="',$scripturl, '?page=',$alink['id'],'"><img title="'.$txt['tp-preview'].'" src="' .$settings['tp_images_url']. '/TPfilter.png" alt="" /></a>
										' , $alink['locked']==0 ?
										'<a href="' . $scripturl . '?action=admin;area=tparticles;sa=editarticle;article='.$alink['id']. '"><img title="'.$txt['tp-edit'].'" src="' .$settings['tp_images_url']. '/TPconfig_sm.png" alt="'.$txt['tp-edit'].'"  /></a>' : '<img title="'.$txt['tp-islocked'].'" src="' .$settings['tp_images_url']. '/TPconfig_sm2.png" alt="'.$txt['tp-islocked'].'"  />' , '
										<img style="cursor: pointer;" class="toggleSticky" id="artSticky' .$alink['id']. '" title="'.$txt['tp-setsticky'].'" src="' .$settings['tp_images_url']. '/TPsticky' , $alink['sticky']=='1' ? '1' : '2' , '.png" alt="'.$txt['tp-setsticky'].'"  />
										<img style="cursor: pointer;" class="toggleLock" id="artLock' .$alink['id']. '" title="'.$txt['tp-setlock'].'" src="' .$settings['tp_images_url']. '/TPlock' , $alink['locked']=='1' ? '1' : '2' , '.png" alt="'.$txt['tp-setlock'].'"  />
										<img style="cursor: pointer;" class="toggleFront" id="artFront' .$alink['id']. '" title="'.$txt['tp-setfrontpage'].'" src="' .$settings['tp_images_url']. '/TPfront' , $alink['frontpage']=='1' ? '' : '2' , '.png" alt="'.$txt['tp-setfrontpage'].'"  />
										<img style="cursor: pointer;" class="toggleFeatured" id="artFeatured' .$alink['id']. '" title="'.$txt['tp-featured'].'" src="' .$settings['tp_images_url']. '/TPflag' , $alink['featured']=='1' ? '' : '2' , '.png" alt="'.$txt['tp-featured'].'"  />
									</div>
								</div>
								<div class="smalltext fullwidth-on-res-layout float-items" style="text-align:center;width:7%;">
									<div class="show-on-responsive"> ' , $context['TPortal']['sort']=='type' ? '<img src="' . $settings['tp_images_url'] . '/TPsort_down.png" alt="'.$txt['tp-sort-on-type'].'" /> ' : '' , '<a class="bbc_strong" title="'.$txt['tp-sort-on-type'].'" href="' . $scripturl . '?action=admin;area=tparticles;sa=submission;sort=type">' , $txt['tp-type'] , '</a></div>
									<div style="text-transform:uppercase;">' , empty($alink['type']) ? 'html' : $alink['type'] , '</div>
									</div>
									<div style="text-align:center;width:6%;" class="smalltext fullwidth-on-res-layout float-items">
										<div class="show-on-responsive bbc_strong">'.$txt['tp-delete'].'</div>
										<a href="' . $scripturl . '?action=admin;area=tparticles;cu=-1;' . $context['session_var'] . '=' . $context['session_id'].';artdelete=' .$alink['id']. '" onclick="javascript:return confirm(\''.$txt['tp-articleconfirmdelete'].'\')">
										<img title="'.$txt['tp-delete'].'" src="' .$settings['tp_images_url']. '/TPdelete2.png" alt="'.$txt['tp-delete'].'"  /></a>
									</div>
								</div>
							</div>
						</td>
						</tr>';
		}
			echo '
					</tbody>
				</table>';

			if( !empty($context['TPortal']['pageindex']))
				echo '
				<div class="middletext padding-div bbc_strong">
					'.$context['TPortal']['pageindex'].'
				</div>';

		if(isset($context['TPortal']['allcats']))
		{
			echo '
				<br><div class="padding-div">
					<select name="tp_article_cat">
						<option value="0">' . $txt['tp-createnew2'] . '</option>';
			foreach($context['TPortal']['allcats'] as $submg)
  					echo '
						<option value="'.$submg['id'].'">'. $txt['tp-approveto'] . $submg['name'].'</option>';
			echo '
					</select>
					<input name="tp_article_new" value="" size="40" /> &nbsp;
				</div>';
		}
		echo '
				<div class="submitbutton"><input type="submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
			</div>';
	}
	else
		echo '
			<div class="content">
				<div class="padding-div">'.$txt['tp-nosubmissions'].'</div>
			</div>';

		echo '
		</div>
	</form>';
}

// Article Settings Page
function template_artsettings()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language, $date;

		echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tparticles;sa=artsettings" method="post">
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input  type="hidden"name="tpadmin_form" value="artsettings">
		<div class="cat_bar"><header class="category_header">' . $txt['tp-articlesettings'] . '</header></div>
		<p class="description">' , $txt['tp-helpartsettings'] , '</p>
		<div id="article-settings" class="generic_list_wrapper">
			<div class="content">
				<div class="formtable padding-div">
					<dl class="settings">
						<dt>
							<label for="tp_use_wysiwyg">', $txt['tp-usewysiwyg'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_use_wysiwyg" name="tp_use_wysiwyg" value="1" ' , $context['TPortal']['use_wysiwyg']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="tp_editorheight">', $txt['tp-editorheight'], '</label>
						</dt>
						<dd>
							<input type="number" id="tp_editorheight" name="tp_editorheight" value="' , $context['TPortal']['editorheight'] , '" style="width: 6em" min="200" />
						</dd>
						<dt>
							<label for="tp_use_dragdrop">', $txt['tp-usedragdrop'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_use_dragdrop" name="tp_use_dragdrop" value="1" ' , $context['TPortal']['use_dragdrop']=='1' ? 'checked' : '' , '>
						</dd>
					</dl>
					<hr>
					<dl class="settings">
						<dt>
							<label for="tp_hide_editarticle_link">', $txt['tp-hidearticle-link'], '&nbsp;&nbsp;<img src="' . $settings['tp_images_url'] . '/TPedit2.png" alt="" /></label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hide_editarticle_link" name="tp_hide_editarticle_link" value="1" ' , $context['TPortal']['hide_editarticle_link']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="tp_print_articles">'.$txt['tp-printarticles'].'&nbsp;&nbsp;<img src="' . $settings['tp_images_url'] . '/TPprint.png" alt="" /></label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_print_articles" name="tp_print_articles" value="1" ' , $context['TPortal']['print_articles']=='1' ? 'checked' : '' , '>
						</dd>
                        <dt>
							<label for="tp_allow_links_article_comments">', $txt['tp-allow-links-article-comments'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_allow_links_article_comments" name="tp_allow_links_article_comments" value="1" ' , $context['TPortal']['allow_links_article_comments']=='1' ? 'checked' : '' , '>
						</dd>
					</dl>
					<hr>
					<dl class="settings">
						<dt>
							<label for="tp_hide_article_facebook">', $txt['tp-hidearticle-facebook'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hide_article_facebook" name="tp_hide_article_facebook" value="1" ' , $context['TPortal']['hide_article_facebook']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="tp_hide_article_twitter">', $txt['tp-hidearticle-twitter'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hide_article_twitter" name="tp_hide_article_twitter" value="1" ' , $context['TPortal']['hide_article_twitter']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="tp_hide_article_reddit">', $txt['tp-hidearticle-reddit'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hide_article_reddit" name="tp_hide_article_reddit" value="1" ' , $context['TPortal']['hide_article_reddit']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="tp_hide_article_digg">', $txt['tp-hidearticle-digg'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hide_article_digg" name="tp_hide_article_digg" value="1" ' , $context['TPortal']['hide_article_digg']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="tp_hide_article_delicious">', $txt['tp-hidearticle-delicious'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hide_article_delicious" name="tp_hide_article_delicious" value="1" ' , $context['TPortal']['hide_article_delicious']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="tp_hide_article_stumbleupon">', $txt['tp-hidearticle-stumbleupon'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hide_article_stumbleupon" name="tp_hide_article_stumbleupon" value="1" ' , $context['TPortal']['hide_article_stumbleupon']=='1' ? 'checked' : '' , '>
						</dd>
					</dl>
					<hr>
					<dl class="settings">
						<dt>
							<label for="tp_icon_width">'.$txt['tp-iconsize'].'</label>
						</dt>
						<dd>
							<input type="number" name="tp_icon_width" id="tp_icon_width" value="'.$context['TPortal']['icon_width'].'" style="width: 6em" maxlength="3"> x <input type="number" name="tp_icon_height"value="'.$context['TPortal']['icon_height'].'" style="width: 6em" maxlength="3" > px
						</dd>
						<dt>
							<label for="tp_iconmaxsize">'.$txt['tp-iconmaxsize'].'</label>
						</dt>
						<dd>
							<input type="number" name="tp_icon_max_size" id="tp_iconmaxsize" value="'.$context['TPortal']['icon_max_size'].'" style="width: 6em" maxlength="4"> '.$txt['tp-kb'].'
						</dd>
					</dl>
				</div>
				<div class="submitbutton"><input type="submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
			</div>
		</div>
	</form>';
}

// Article icons Page
function template_articons()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

		echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" enctype="multipart/form-data" action="' . $scripturl . '?action=admin;area=tparticles;sa=articons" method="post">
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="tpadmin_form" value="articons">
		<div class="cat_bar"><header class="category_header">' . $txt['tp-adminicons7'] . '</header></div>
		<p class="description">' , $txt['tp-adminiconsinfo'] , '</p>
		<div id="article-icons-pictures" class="generic_list_wrapper">
			<div class="content">
				<div class="formtable">
					<dl class="tptitle settings">
						<dt>
							<label for="tp_article_newillustration">', $txt['tp-adminicons6'], '</label>
						</dt>
						<dd>
							<input type="file" name="tp_article_newillustration" id="tp_article_newillustration">
						</dd>
					</dl>
					<hr><br>';

		$alt = true;
		if(count($context['TPortal']['articons']['illustrations'])>0) {
			foreach($context['TPortal']['articons']['illustrations'] as $icon) {
				echo '
					<div class="smalltext padding-div floatleft">
						<div style="width: 110px; height: 110px;text-align:center;">
							<div class="article_icon"><img src="' . $icon['background'] . '" alt="'.$icon['file'].'" title="'.$icon['file'].'"></div>
						</div>
						<div>
							<input type="checkbox" id="artillustration'.$icon['id'].'" name="artillustration'.$icon['id'].'" style="vertical-align: top;" value="'.$icon['file'].'"  /> <label style="vertical-align: top;"  for="artiillustration'.$icon['id'].'">'.$txt['tp-remove'].'</label>
						</div>
					</div>
							';
				$alt = !$alt;
			}
		}

		echo '
					<hr>
				</div>
				<div class="submitbutton"><input type="submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
			</div>
		</div>
	</form>';
}

?>
