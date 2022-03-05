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
use TinyPortal\Model\Subs as TPSubs;

// Edit Block Page (including settings per block type)
function template_editblock()
{
	global $context, $settings, $txt, $scripturl, $boardurl;

	$newtitle = html_entity_decode(TPSubs::getInstance()->getlangOption($context['TPortal']['blockedit']['lang'], $context['user']['language']));
	if(empty($newtitle)) {
		$newtitle = html_entity_decode($context['TPortal']['blockedit']['title']);
	}

	echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" enctype="multipart/form-data" action="' . $scripturl . '?action=admin;area=tpblocks;sa=updateblock;id='.$context['TPortal']['blockedit']['id'].'" method="post" onsubmit="submitonce(this);">
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="tpadmin_form" value="blockedit">
		<input type="hidden" name="tpadmin_form_id" value="' . $context['TPortal']['blockedit']['id'] . '">
		<div class="cat_bar"><h3 class="category_header">' . $txt['tp-editblock'] . '</h3></div>
		<div id="editblock" class="admintable admin-area">
			<div class="content padding-div">
				<div class="formtable">
					<dl class="tptitle settings">
						<dt>
							<b><label for="tp_block_off">', $txt['tp-status'], '<img style="margin:0 1ex;" src="' . $settings['tp_images_url'] . '/TP' , $context['TPortal']['blockedit']['off']==0 ? 'green' : 'red' , '.png" alt="" /></label></b>
						</dt>
						<dd>
							<input type="radio" value="0" name="tp_block_off" id="tp_block_off"',$context['TPortal']['blockedit']['off']==0 ? ' checked="checked"' : '' ,' />'.$txt['tp-on'].'
							<input type="radio" value="1" name="tp_block_off"',$context['TPortal']['blockedit']['off']==1 ? ' checked="checked"' : '' ,' />'.$txt['tp-off'].'
						</dd>
					</dl>
					<dl class="tptitle settings">
						<dt>
							<label for="tp_block_title"><b>'.$txt['tp-title'].'</b></label>
						</dt>
						<dd>
							<input type="text" id="tp_block_title" name="tp_block_title" value="' .$newtitle. '" size=60 required><br><br>
						</dd>
						<dt><label for="tp_block_type"><b>',$txt['tp-type'].'</b></label></dt>
						<dd>
							<select size="1" onchange="document.getElementById(\'blocknotice\').style.display=\'\';" name="tp_block_type" id="tp_block_type">
								<option value="0"' ,$context['TPortal']['blockedit']['type']=='0' ? ' selected' : '' , '>', $txt['tp-blocktype0'] , '</option>
								<option value="8"' ,$context['TPortal']['blockedit']['type']=='8' ? ' selected' : '' , '>', $txt['tp-blocktype8'] , '</option>
								<option value="18"' ,$context['TPortal']['blockedit']['type']=='18' ? ' selected' : '' , '>', $txt['tp-blocktype18'] , '</option>
								<option value="19"' ,$context['TPortal']['blockedit']['type']=='19' ? ' selected' : '' , '>', $txt['tp-blocktype19'] , '</option>
								<option value="5"' ,$context['TPortal']['blockedit']['type']=='5' ? ' selected' : '' , '>', $txt['tp-blocktype5'] , '</option>
								<option value="11"' ,$context['TPortal']['blockedit']['type']=='11' ? ' selected' : '' , '>', $txt['tp-blocktype11'] , '</option>
								<option value="10"' ,$context['TPortal']['blockedit']['type']=='10' ? ' selected' : '' , '>', $txt['tp-blocktype10'] , '</option>
								<option value="2"' ,$context['TPortal']['blockedit']['type']=='2' ? ' selected' : '' , '>', $txt['tp-blocktype2'] , '</option>
								<option value="6"' ,$context['TPortal']['blockedit']['type']=='6' ? ' selected' : '' , '>', $txt['tp-blocktype6'] , '</option>
								<option value="12"' ,$context['TPortal']['blockedit']['type']=='12' ? ' selected' : '' , '>', $txt['tp-blocktype12'] , '</option>
								<option value="15"' ,$context['TPortal']['blockedit']['type']=='15' ? ' selected' : '' , '>', $txt['tp-blocktype15'] , '</option>
								<option value="4"' ,$context['TPortal']['blockedit']['type']=='4' ? ' selected' : '' , '>', $txt['tp-blocktype4'] , '</option>
								<option value="13"' ,$context['TPortal']['blockedit']['type']=='13' ? ' selected' : '' , '>', $txt['tp-blocktype13'] , '</option>
								<option value="3"' ,$context['TPortal']['blockedit']['type']=='3' ? ' selected' : '' , '>', $txt['tp-blocktype3'] , '</option>
								<option value="7"' ,$context['TPortal']['blockedit']['type']=='7' ? ' selected' : '' , '>', $txt['tp-blocktype7'] , '</option>
								<option value="1"' ,$context['TPortal']['blockedit']['type']=='1' ? ' selected' : '' , '>', $txt['tp-blocktype1'] , '</option>
							</select>
						</dd>
						<dt>
							<br><div class="padding-div"><input type="submit" class="button button_submit" value="' . $txt['tp-send'] . '" /></div>
						</dt>
						<dd>
							<div>
								<div id="blocknotice" class="smallpadding error middletext" style="display: none;">' , $txt['tp-blocknotice'] , '</div>
							</div>
						</dd>
					</dl>
					<div class="content padding-div">
					 <div>';
            
            $blockClass = '\TinyPortal\Blocks\\'.ucfirst(str_replace('box', '', \TinyPortal\Model\Block::getInstance()->getBlockType($context['TPortal']['blockedit']['type'])));
            if(class_exists($blockClass) && method_exists($blockClass, 'admin_display')) {
                $found = (new $blockClass)->admin_display($context['TPortal']['blockedit']);
            }

			if($found == false) {
	// Block type: Single Article
				if($context['TPortal']['blockedit']['type']=='18'){
					// check to see if it is numeric
					if(!is_numeric($context['TPortal']['blockedit']['body']))
						$lblock['body']='';
					echo '
						</div><div>
						<hr><dl class="tptitle settings">
							<dt>
								<label for="field_name">',$txt['tp-showarticle'],'</label>
							</dt>
							<dd>
								<select name="tp_block_body">
								<option value="0">'.$txt['tp-none2'].'</option>';
					foreach($context['TPortal']['edit_articles'] as $art => $article ){
						echo '<option value="'.$article['id'].'" ' , $context['TPortal']['blockedit']['body']==$article['id'] ? ' selected="selected"' : '' ,' >'.html_entity_decode($article['subject']).'</option>';
					}
					echo '</select>
							</dd>
						</dl>';
				}
	// Block type: Themes
				elseif($context['TPortal']['blockedit']['type']=='7') {
					// get the ids
					$myt=array();
					$thems=explode(",",$context['TPortal']['blockedit']['body']);
					foreach($thems as $g => $gh)
					{
						$wh=explode("|",$gh);
						$myt[]=$wh[0];
					}
						echo '
							<hr><input type="hidden" name="blockbody' .$context['TPortal']['blockedit']['id']. '" value="' .$context['TPortal']['blockedit']['body'] . '" />
							<div style="padding: 5px;">
								<div style="max-height: 25em; overflow: auto;">
								<input type="hidden" name="tp_theme-1" value="-1">
								<input type="hidden" name="tp_tpath-1" value="1">';
					foreach($context['TPthemes'] as $tema)
					{
							echo '
								<img class="theme_icon" alt="*" src="'.$tema['path'].'/thumbnail.png" /> <input type="checkbox" name="tp_theme'.$tema['id'].'" value="'.$tema['name'].'"';
						if(in_array($tema['id'],$myt))
							echo ' checked';
						echo '>'.$tema['name'].'<input type="hidden" value="'.$tema['path'].'" name="tp_path'.$tema['id'].'"><br>';
					}
					echo '
						</div>
						<br>
						<input type="checkbox" onclick="invertAll(this, this.form, \'tp_theme\');" /> '.$txt['tp-checkall'],'
					';
				}
	// Block type: Articles in a Category
				elseif($context['TPortal']['blockedit']['type']=='19') {
					if(!is_numeric($context['TPortal']['blockedit']['body']))
						$lblock['body']='';
					echo '
						<hr><dl class="tptitle settings">
							<dt>
								<label for="tp_block_body">'.$txt['tp-showcategory'].'</label>
							</dt>
							<dd>
								<select name="tp_block_body" id="tp_block_body">
								<option value="0">'.$txt['tp-none2'].'</option>';
					foreach($context['TPortal']['catnames'] as $cat => $catname){
						echo '
									<option value="'.$cat.'" ' , $context['TPortal']['blockedit']['body']==$cat ? ' selected' : '' ,' >'.html_entity_decode($catname).'</option>';
					}
					echo '
								</select>
							</dd>
							<dt>
								<label for="tp_block_var1">'.$txt['tp-catboxheight'].'</label>
							</dt>
							<dd>
								<input type="number" id="tp_block_var1" name="tp_block_var1" value="' , ((!is_numeric($context['TPortal']['blockedit']['var1'])) || (($context['TPortal']['blockedit']['var1']) == 0) ? '15' : $context['TPortal']['blockedit']['var1']) ,'" style="width: 6em" min="1" required> em
							</dd>
							<dt>
								<label for="field_name">'.$txt['tp-catboxauthor'].'</label>
							</dt>
							<dd>
								<input type="radio" name="tp_block_var2" value="1" ' , $context['TPortal']['blockedit']['var2']=='1' ? 'checked' : '' ,'> ', $txt['tp-yes'], '<br>
								<input type="radio" name="tp_block_var2" value="0" ' , $context['TPortal']['blockedit']['var2']=='0' ? 'checked' : '' ,'> ', $txt['tp-no'], '
							</dd>
						</dl>';
				}
	// Block type: Online
				elseif($context['TPortal']['blockedit']['type']=='6') {
					echo '
						<hr><dl class="tptitle settings">
							<dt>
								<label for="field_name">'.$txt['tp-rssblock-showavatar'].'</label>
							</dt>
							<dd>
								<input type="radio" name="tp_block_var1" value="1" ' , ($context['TPortal']['blockedit']['var1']=='1' || $context['TPortal']['blockedit']['var1']=='') ? ' checked' : '' ,'>'.$txt['tp-yes'].' <input type="radio" name="tp_block_var1" value="0" ' , $context['TPortal']['blockedit']['var1']=='0' ? ' checked' : '' ,'>'.$txt['tp-no'].'
							</dd>
						</dl>';
				}
				else {
					echo '
				</div><div>';
				}
			}

			echo '
					</div>
				</div>
				<div><hr>
					<div>
					<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-blockstylehelpdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
					<label for="field_name">'.$txt['tp-blockstylehelp'].'</label>
					</div>
					<br><input type="radio" id="tp_block_var5" name="tp_block_var5" value="99" ' , $context['TPortal']['blockedit']['var5']=='99' ? 'checked' : '' , '><span' , $context['TPortal']['blockedit']['var5']=='99' ? ' style="color: red;">' : '><label for="tp_block_var5">' , $txt['tp-blocksusepaneltyle'] , '</label></span>
				<div>
				<div class="panels-optionsbg">';

			$types = TPSubs::getInstance()->getBlockStyles();

			foreach($types as $blo => $bl) {
				echo '
					<div class="panels-options">
						<div>
							<input type="radio" id="tp_block_var5'.$blo.'" name="tp_block_var5" value="'.$blo.'" ' , $context['TPortal']['blockedit']['var5']==$blo ? 'checked' : '' , '><label for="tp_block_var5'.$blo.'"><span' , $context['TPortal']['blockedit']['var5']==$blo ? ' style="color: red;">' : '>' , $bl['class'] , '</span></label>
						</div>
						' . $bl['code_title_left'] . 'title'. $bl['code_title_right'].'
						' . $bl['code_top'] . 'body' . $bl['code_bottom'] . '
					</div>';
            }

			echo '
						</div>
					</div>
				</div>
				<br>
					<dl class="settings">
						<dt>
							<label for="field_name">'.$txt['tp-blockframehelp'].'</label>
						</dt>
						<dd>
							<input type="radio" id="useframe" name="tp_block_frame" value="theme" ' , $context['TPortal']['blockedit']['frame']=='theme' ? 'checked' : '' , '><label for="useframe"> '.$txt['tp-useframe'].'</label><br>
							<input type="radio" id="useframe2" name="tp_block_frame" value="frame" ' , $context['TPortal']['blockedit']['frame']=='frame' ? 'checked' : '' , '><label for="useframe2"> '.$txt['tp-useframe2'].' </label><br>
							<input type="radio" id="usetitle" name="tp_block_frame" value="title" ' , $context['TPortal']['blockedit']['frame']=='title' ? 'checked' : '' , '><label for="usetitle"> '.$txt['tp-usetitle'].' </label></br>
							<input type="radio" id="noframe" name="tp_block_frame" value="none" ' , $context['TPortal']['blockedit']['frame']=='none' ? 'checked' : '' , '><label for="noframe"> '.$txt['tp-noframe'].'</label>
						</dd>
					</dl>
					<br>
					<dl class="settings">
						<dt>
							<label for="field_name">'.$txt['tp-allowupshrink'].'</label>
						</dt>
						<dd>
							<input type="radio" id="allowupshrink" name="tp_block_visible" value="1" ' , ($context['TPortal']['blockedit']['visible']=='' || $context['TPortal']['blockedit']['visible']=='1') ? 'checked' : '' , '><label for="allowupshrink"> '.$txt['tp-allowupshrink'].'</label><br>
							<input type="radio" id="notallowupshrink" name="tp_block_visible" value="0" ' , ($context['TPortal']['blockedit']['visible']=='0') ? 'checked' : '' , '><label for="notallowupshrink"> '.$txt['tp-notallowupshrink'].'</label>
						</dd>
					</dl>
					<br>
					<dl class="settings">
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-membergrouphelpdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="field_name">'.$txt['tp-membergrouphelp'].'</label>
						</dt>
						<dd><div>
							  <div class="tp_largelist">';
			// loop through and set membergroups
			$tg=explode(',',$context['TPortal']['blockedit']['access']);
			if( !empty($context['TPmembergroups'])) {
				foreach($context['TPmembergroups'] as $mg) {
					if($mg['posts']=='-1' && $mg['id']!='1'){
						echo '<input type="checkbox" id="tp_group'.$mg['id'].'" name="tp_group'.$mg['id'].'" value="'.$context['TPortal']['blockedit']['id'].'"';
						if(in_array($mg['id'],$tg)) {
							echo ' checked';
                        }
						echo '><label for="tp_group'.$mg['id'].'"> '.$mg['name'].'</label><br>';
					}
				}
			}
			// if none is chosen, have a control value
			echo '
							</div>
								<input type="checkbox" id="checkallmg" onclick="invertAll(this, this.form, \'tp_group\');" /><label for="checkallmg">'.$txt['tp-checkall'].'</label><br>
							</div>
						</dd>
					</dl>';
			//edit membergroups
			echo '
					<dl class="settings">
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-editgrouphelpdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="field_name">'.$txt['tp-editgrouphelp'].'</label>
						</dt>
						<dd>
							<div>
								<div class="tp_largelist">';
			$tg=explode(',',$context['TPortal']['blockedit']['editgroups']);
			foreach($context['TPmembergroups'] as $mg){
				if($mg['posts']=='-1' && $mg['id']!='1' && $mg['id']!='-1' && $mg['id']!='0'){
					echo '<input type="checkbox" id="tp_editgroup'.$mg['id'].'" name="tp_editgroup'.$mg['id'].'" value="'.$context['TPortal']['blockedit']['id'].'"';
					if(in_array($mg['id'],$tg))
						echo ' checked';
					echo '><label for="tp_editgroup'.$mg['id'].'"> '.$mg['name'].'</label><br>';
				}
			}
			// if none is chosen, have a control value
			echo '				</div><input type="checkbox" id="checkalleditmg" onclick="invertAll(this, this.form, \'tp_editgroup\');" /><label for="checkalleditmg">'.$txt['tp-checkall'];
			echo '				</label><br>
							</div>
						</dd>
					</dl>
					<dl class="settings">
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-langhelpdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="field_name">'.$txt['tp-langhelp'].'</label>
						</dt>
						<dd>
							<div>';
			foreach($context['TPortal']['langfiles'] as $langlist => $lang){
				if($lang!=$context['user']['language'] && $lang!='')
					echo '<input type="text" name="tp_lang_'.$lang.'" value="' , !empty($context['TPortal']['blockedit']['langfiles'][$lang]) ? html_entity_decode($context['TPortal']['blockedit']['langfiles'][$lang], ENT_QUOTES) : html_entity_decode($context['TPortal']['blockedit']['title'],ENT_QUOTES) , '" size="50"> '. $lang.'<br>';
			}
			echo '			</div>
						<br></dd>
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-langdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="field_name">' . $txt['tp-lang'] . '</label>';
				// alert if the settings is off, supply link if allowed
				if(empty($context['TPortal']['uselangoption'])) {
					echo '
					<br><span class="error">', $txt['tp-uselangoption2'] , ' ' , allowedTo('tp_settings') ? '<a href="'.$scripturl.'?action=admin;area=tpblocks;sa=settings#uselangoption">&nbsp;['. $txt['tp-settings'] .']&nbsp;</a>' : '' , '</span>';
				}
				echo '
					</dt>
					<dd>';
				$a=1;
				foreach($context['TPortal']['langfiles'] as $bb => $lang) {
					echo '
							<input type="checkbox" id="langtype' . $a . '" name="langtype' . $a . '" value="'.$lang.'" ' , in_array($lang, $context['TPortal']['blockedit']['display']['lang']) ? 'checked="checked"' : '' , '><label for="langtype' . $a . '"> '.$lang.'</label><br>';
					$a++;
				}
				echo ' 
					</dd>
				</dl>';

		if($context['TPortal']['blockedit']['bar']!=4) {
			// extended visible options
				echo '
					<div class="admintable">
						<div>'.$txt['tp-displayhelp'].'</div>
						<div id="collapse-options">
						', TPSubs::getInstance()->hidePanel('blockopts', true) , '
				' , empty($context['TPortal']['blockedit']['display2']) ? '<div class="tborder error" style="margin: 1em 0; padding: 4px 4px 4px 20px;">' . $txt['tp-noaccess'] . '</div>' : '' , '
						<fieldset class="tborder" id="blockopts" ' , in_array('blockopts',$context['tp_panels']) ? ' style="display: none;"' : '' , '>
						<input type="hidden" name="TPadmin_blocks_vo" value="'.$mg['id'].'" />';
				if(!empty($context['TPortal']['return_url']))
					echo '
							<input type="hidden" name="fromblockpost" value="'.$context['TPortal']['return_url'].'" />';
					echo '
					<dl class="settings">
						<dt><label for="field_name">' . $txt['tp-actions'] . '</label></dt>
						<dd>
							<div>
								<input name="actiontype1" id="actiontype1" type="checkbox" value="allpages" ' ,in_array('allpages',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype1"> '.$txt['tp-allpages'].'</label><br><br>
								<input name="actiontype2" id="actiontype2" type="checkbox" value="frontpage" ' ,in_array('frontpage',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype2"> '.$txt['tp-frontpage'].'</label><br>
								<input name="actiontype3" id="actiontype3" type="checkbox" value="forumall" ' ,in_array('forumall',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype3"> '.$txt['tp-forumall'].'</label><br>
								<input name="actiontype4" id="actiontype4" type="checkbox" value="forum" ' ,in_array('forum',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype4"> '.$txt['tp-forumfront'].'</label><br>
								<input name="actiontype5" id="actiontype5" type="checkbox" value="recent" ' ,in_array('recent',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype5"> '.$txt['tp-recent'].'</label><br>
								<input name="actiontype6" id="actiontype6" type="checkbox" value="unread" ' ,in_array('unread',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype6"> '.$txt['tp-unread'].'</label><br>
								<input name="actiontype7" id="actiontype7" type="checkbox" value="unreadreplies" ' ,in_array('unreadreplies',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype7"> '.$txt['tp-unreadreplies'].'</label><br>
								<input name="actiontype8" id="actiontype8" type="checkbox" value="profile" ' ,in_array('profile',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype8"> '.$txt['profile'].'</label><br>
								<input name="actiontype9" id="actiontype9" type="checkbox" value="pm" ' ,in_array('pm',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype9"> '.$txt['pm_short'].'</label><br>
								<input name="actiontype10" id="actiontype10" type="checkbox" value="calendar" ' ,in_array('calendar',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype10"> '.$txt['calendar'].'</label><br>
								<input name="actiontype11" id="actiontype11" type="checkbox" value="admin" ' ,in_array('admin',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype11"> '.$txt['admin'].'</label><br>
								<input name="actiontype12" id="actiontype12" type="checkbox" value="login" ' ,in_array('login',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype12"> '.$txt['login'].'</label><br>
								<input name="actiontype13" id="actiontype13" type="checkbox" value="logout" ' ,in_array('logout',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype13"> '.$txt['logout'].'</label><br>
								<input name="actiontype14" id="actiontype14" type="checkbox" value="register" ' ,in_array('register',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype14"> '.$txt['register'].'</label><br>
								<input name="actiontype15" id="actiontype15" type="checkbox" value="post" ' ,in_array('post',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype15"> '.$txt['post'].'</label><br>
								<input name="actiontype16" id="actiontype16" type="checkbox" value="stats" ' ,in_array('stats',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype16"> '.$txt['tp-stats'].'</label><br>
								<input name="actiontype17" id="actiontype17" type="checkbox" value="search" ' ,in_array('search',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype17"> '.$txt['search'].'</label><br>
								<input name="actiontype18" id="actiontype18" type="checkbox" value="mlist" ' ,in_array('mlist',$context['TPortal']['blockedit']['display']['action']) ? 'checked="checked"' : '' , '><label for="actiontype18"> '.$txt['tp-memberlist'].'</label><br><br>';
					// add the custom ones you added
					$count=19;
					foreach($context['TPortal']['blockedit']['display']['action'] as $po => $p) {
						if(!in_array($p, array('allpages','frontpage','forumall','forum','recent','unread','unreadreplies','profile','pm','calendar','admin','login','logout','register','post','stats','search','mlist')))
						{
							echo '<input type="checkbox" id="actiontype'.$count.'" name="actiontype'.$count.'" value="'.$p.'" checked="checked"><label for="name="actiontype'.$count.'">'.$p.'</label><br>';
							$count++;
						}
					}
					echo '
							<p><label for="custotype0">'.$txt['tp-customactions'].'</label></p>
								<input type="text"id="custotype0" name="custotype0"  value="" style="width: 90%;">
								</div>
							</dd>
					</dl>
					<dl class="settings">
						<dt><label for="field_name">' . $txt['tp-boards'] . '</label></dt>
						<dd>
							<div class="tp_largelist">';
				$a=1;
				if(!empty($context['TPortal']['boards']))
				{
					echo '<input type="checkbox" name="boardtype' , $a, '" value="-1" id="allboards" ' , in_array('-1', $context['TPortal']['blockedit']['display']['board']) ? 'checked="checked"' : '' , '><label for="allboards"> '.$txt['tp-allboards'].'</label><br><br>';
					$a++;
					foreach($context['TPortal']['boards'] as $bb)
					{
						echo '
								<input type="checkbox" name="boardtype' , $a, '" id="boardtype' , $a, '" value="'.$bb['id'].'" ' , in_array($bb['id'], $context['TPortal']['blockedit']['display']['board']) ? 'checked="checked"' : '' , '><label for="boardtype' , $a, '"> '.$bb['name'].'</label><br>';
						$a++;
					}
				}
				echo '
							 </div>
						</dd>
					</dl>
					<dl class="settings">
						<dt><label for="field_name">' . $txt['tp-articles'] . '</label></dt>
						<dd>
							 <div class="tp_largelist">';
				$a=1;
				foreach($context['TPortal']['edit_articles'] as $bb)
				{
					echo '
								<input type="checkbox" id="articletype' , $a , '" name="articletype' , $a , '" value="'.$bb['id'].'" ' ,in_array($bb['id'], $context['TPortal']['blockedit']['display']['page']) ? 'checked="checked"' : '' , '><label for="articletype' , $a , '"> '.html_entity_decode($bb['subject'],ENT_QUOTES).'</label><br>';
					$a++;
				}
				// if none is chosen, have a control value
				echo '</div><input type="checkbox" id="togglearticle" onclick="invertAll(this, this.form, \'articletype\');" /><label for="togglearticle">'.$txt['tp-checkall'];
				echo '</label><br>
						</dd>
					</dl>
					<dl class="settings">
						<dt><label for="field_name">' . $txt['tp-artcat'] . '</label></dt>
						<dd>
						    <div class="tp_largelist">';
				$a=1;
				if(isset($context['TPortal']['article_categories']))
				{
					foreach($context['TPortal']['article_categories'] as $bb)
					{
						echo '
								<input type="checkbox" id="categorytype' . $a . '" name="categorytype' . $a . '" value="'.$bb['id'].'" ' , in_array($bb['id'], $context['TPortal']['blockedit']['display']['cat']) ? 'checked="checked"' : '' , '><label for="categorytype' . $a . '"> '.$bb['name'].'</label><br>';
						$a++;
					}
				}
				// if none is chosen, have a control value
				echo '</div><input type="checkbox" id="togglecat" onclick="invertAll(this, this.form, \'categorytype\');" /><label for="togglecat">'.$txt['tp-checkall'];

				echo '</label<br>
						</dd>
					</dl>
				</fieldset>
				</div>
			</div>';
		}
			echo '
				<div class="padding-div"><input type="submit" class="button button_submit" value="'.$txt['tp-send'].'" name="'.$txt['tp-send'].'"></div>
			</div>
		</div>
	</form>';
}

// Panel Settings Page
function template_panels()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

	echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tpblocks;sa=updatepanels" method="post">
		<input type="hidden" name="sc" value="', $context['session_id'] ,'" />
		<input type="hidden" name="tpadmin_form" value="panels">
		<div class="cat_bar"><h3 class="category_header">' . $txt['tp-panelsettings'] . '</h3></div>
			<div id="panels-admin" class="admintable admin-area">
			<div class="information smalltext">', $txt['tp-helppanels'] ,'</div><div></div>
			<div class="content">
				<div class="formtable padding-div">
					<dl class="settings">
						<dt>
							<strong>', $txt['tp-hidebarsall'] ,'</strong>
						</dt>
						<dd></dd>
						<dt>
							<label for="tp_hidebars_admin_only">', $txt['tp-hidebarsadminonly'] ,'</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hidebars_admin_only" name="tp_hidebars_admin_only" value="1" ', $context['TPortal']['hidebars_admin_only']=='1' ? 'checked' : '' ,'>
						</dd>
						<dt>
							<label for="tp_hidebars_profile">', $txt['tp-hidebarsprofile'] ,'</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hidebars_profile" name="tp_hidebars_profile" value="1" ' , $context['TPortal']['hidebars_profile']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="tp_hidebars_pm">', $txt['tp-hidebarspm'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hidebars_pm" name="tp_hidebars_pm" value="1" ' , $context['TPortal']['hidebars_pm']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="tp_hidebars_memberlist">', $txt['tp-hidebarsmemberlist'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hidebars_memberlist" name="tp_hidebars_memberlist" value="1" ' , $context['TPortal']['hidebars_memberlist']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="tp_hidebars_search">', $txt['tp-hidebarssearch'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hidebars_search" name="tp_hidebars_search" value="1" ' , $context['TPortal']['hidebars_search']=='1' ? 'checked' : '' , '>
						</dd>
						<dt>
							<label for="tp_hidebars_calendar">', $txt['tp-hidebarscalendar'], '</label>
						</dt>
						<dd>
							<input type="checkbox" id="tp_hidebars_calendar" name="tp_hidebars_calendar" value="1" ' , $context['TPortal']['hidebars_calendar']=='1' ? 'checked' : '' , '>
						</dd>
					</dl>
					<dl class="settings">
						<dt>
							<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-hidebarscustomdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
							<label for="tp_hidebars_custom">'.$txt['tp-hidebarscustom'].'</label>
						</dt>
						<dd>
							<textarea cols="40" style="width: 94%; height: 100px;" name="tp_hidebars_custom" id="tp_hidebars_custom">' . $context['TPortal']['hidebars_custom'].'</textarea>
						</dd>
					</dl>
					<dl class="settings">
						<dt>
							<label for="tp_padding">'.$txt['tp-padding_between'].'</label>
						</dt>
						<dd>
							<input type="number" id="tp_padding" name="tp_padding" value="' ,$context['TPortal']['padding'], '" style="width: 6em" maxlength="5">
							<span class="smalltext">'.$txt['tp-inpixels'].'</span>
						</dd>
					</dl>
					<div class="padding-div"><input type="submit" class="button button_submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
				</div>';

	$allpanels = array('left','right','top','center','front','lower','bottom');
	$alternate = true;

	$types = TPSubs::getInstance()->getBlockStyles();

	foreach($allpanels as $pa => $panl) {
		echo '
				<div id="panels-options" class="padding-div">
				<hr>
				<dl class="settings">
				<dt>
					<div class="font-strong">';
		if( $panl != 'front' ) {
			echo $txt['tp-'.$panl.'panel'].'</div></dt>
				<dd>
					<a name="'.$panl.'"></a><img src="' .$settings['tp_images_url']. '/TPpanel_'.$panl.'' , $context['TPortal']['admin'.$panl.'panel'] ? '' : '_off' , '.png" alt="" /></dd>';
        }
		else {
			echo $txt['tp-'.$panl.'panel'].'</div></dt>
					<a name="'.$panl.'"></a><img src="' .$settings['tp_images_url']. '/TPpanel_'.$panl.'.png" alt="" /></dd>';
        }
		echo '
					<br>
				</dl>
				<dl class="settings">';
		if( $panl != 'front' ) {
			if(in_array($panl, array("left","right")))
				echo '
					<dt>
						<label for="tp_'.$panl.'bar_width">'.$txt['tp-panelwidth'].'</label>
					</dt>
					<dd>
						<input type="text" id="tp_'.$panl.'bar_width" name="tp_'.$panl.'bar_width" value="' , $context['TPortal'][$panl. 'bar_width'] , '" size="5" maxlength="5"><br>
					</dd>';
				echo '
					<dt>
						<label for="tp_'.$panl.'panel">'.$txt['tp-use'.$panl.'panel'].'</label>
					</dt>
					<dd>
						<input type="radio" id="tp_'.$panl.'panel" name="tp_'.$panl.'panel" value="1" ' , $context['TPortal']['admin'.$panl.'panel']==1 ? 'checked' : '' , '> '.$txt['tp-on'].'
						<input type="radio" name="tp_'.$panl.'panel" value="0" ' , $context['TPortal']['admin'.$panl.'panel']==0 ? 'checked' : '' , '> '.$txt['tp-off'].'<br>
					</dd>
					<dt>
						<label for="tp_hide_'.$panl.'bar_forum">'.$txt['tp-hide_'.$panl.'bar_forum'].'</label>
					</dt>
					<dd>
						<input type="radio" id="tp_hide_'.$panl.'bar_forum" name="tp_hide_'.$panl.'bar_forum" value="1" ' , $context['TPortal']['hide_'.$panl.'bar_forum']==1 ? 'checked' : '' , '> '.$txt['tp-yes'].'
						<input type="radio" name="tp_hide_'.$panl.'bar_forum" value="0" ' , $context['TPortal']['hide_'.$panl.'bar_forum']==0 ? 'checked' : '' , '> '.$txt['tp-no'].'
						<br><br>
					</dd>';
		}
		echo '
					<dt>
						<label for="tp_block_layout_'.$panl.'1">'.$txt['tp-vertical'].'</label>
					</dt>
					<dd>
						<input type="radio" id="tp_block_layout_'.$panl.'1" name="tp_block_layout_'.$panl.'" value="vert" ' , $context['TPortal']['block_layout_'.$panl]=='vert' ? 'checked' : '' , '>
					</dd>
					<dt>
						<label for="tp_block_layout_'.$panl.'2">'.$txt['tp-horisontal'].'</label>
					</dt>
					<dd>
						<input type="radio" id="tp_block_layout_'.$panl.'2" name="tp_block_layout_'.$panl.'" value="horiz" ' , $context['TPortal']['block_layout_'.$panl]=='horiz' ? 'checked' : '' , '>
					</dd>
					<dt>
						<label for="tp_block_layout_'.$panl.'3">'.$txt['tp-horisontal2cols'].'</label>
					</dt>
					<dd>
						<input type="radio" id="tp_block_layout_'.$panl.'3" name="tp_block_layout_'.$panl.'" value="horiz2" ' , $context['TPortal']['block_layout_'.$panl]=='horiz2' ? 'checked' : '' , '>
					</dd>
					<dt>
						<label for="tp_block_layout_'.$panl.'4">'.$txt['tp-horisontal3cols'].'</label>
					</dt>
					<dd>
						<input type="radio" id="tp_block_layout_'.$panl.'4" name="tp_block_layout_'.$panl.'" value="horiz3" ' , $context['TPortal']['block_layout_'.$panl]=='horiz3' ? 'checked' : '' , '>
					</dd>
					<dt>
						<label for="tp_block_layout_'.$panl.'5">'.$txt['tp-horisontal4cols'].'</label>
					</dt>
					<dd>
						<input type="radio" id="tp_block_layout_'.$panl.'5" name="tp_block_layout_'.$panl.'" value="horiz4" ' , $context['TPortal']['block_layout_'.$panl]=='horiz4' ? 'checked' : '' , '>
					</dd>
					<dt>
						<label for="tp_block_layout_'.$panl.'6">'.$txt['tp-grid'].'</label>
					</dt>
					<dd>
						<input type="radio" id="tp_block_layout_'.$panl.'6" name="tp_block_layout_'.$panl.'" value="grid" ' , $context['TPortal']['block_layout_'.$panl]=='grid' ? 'checked' : '' , '>
					</dd>
					<dt>&nbsp;</dt>
					<dd>
						<hr><p>
						<input type="radio" id="tp_blockgrid_'.$panl.'1" name="tp_blockgrid_'.$panl.'" value="colspan3" ' , $context['TPortal']['blockgrid_'.$panl]=='colspan3' ? 'checked' : '' , ' /><label for="tp_blockgrid_'.$panl.'1"><img src="' .$settings['tp_images_url']. '/TPgrid1.png" alt="colspan3" /></label>
						<input type="radio" id="tp_blockgrid_'.$panl.'2" name="tp_blockgrid_'.$panl.'" value="rowspan1" ' , $context['TPortal']['blockgrid_'.$panl]=='rowspan1' ? 'checked' : '' , ' /><label for="tp_blockgrid_'.$panl.'2"><img src="' .$settings['tp_images_url']. '/TPgrid2.png" alt="rowspan1" /></label></p>
					</dd>
				</dl>
				<dl class="settings">
					<dt>
						<label for="tp_blockwidth_'.$panl.'">'.$txt['tp-blockwidth'].':</label>
					</dt>
					<dd>
						<input type="text" id="tp_blockwidth_'.$panl.'" name="tp_blockwidth_'.$panl.'" value="' ,$context['TPortal']['blockwidth_'.$panl], '" size="5" maxlength="5"><br>
					</dd>
					<dt>
						<label for="tp_blockheight_'.$panl.'">'.$txt['tp-blockheight'].':</label>
					</dt>
					<dd>
						<input type="text" id="tp_blockheight_'.$panl.'" name="tp_blockheight_'.$panl.'" value="' ,$context['TPortal']['blockheight_'.$panl], '" size="5" maxlength="5">
					</dd>
				</dl>
				<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=',$txt['tp-panelstylehelpdesc'],'" onclick="return reqOverlayDiv(this.href);"></a>
				<label>'.$txt['tp-panelstylehelp'].'</label>
				<div class="panels-optionsbg">';

			foreach($types as $blo => $bl)
				echo '
					<div class="panels-options">
						<div class="smalltext" style="padding: 4px 0;">
							<input type="radio" id="tp_panelstyle_'.$panl.''.$blo.'" name="tp_panelstyle_'.$panl.'" value="'.$blo.'" ' , $context['TPortal']['panelstyle_'.$panl]==$blo ? 'checked' : '' , '><label for="tp_panelstyle_'.$panl.''.$blo.'">
							<span' , $context['TPortal']['panelstyle_'.$panl]==$blo ? ' style="color: red;">' : '>' , $bl['class'] , '</span></label>
						</div>
						' . $bl['code_title_left'] . 'title'. $bl['code_title_right'].'
						' . $bl['code_top'] . 'body' . $bl['code_bottom'] . '
					</div>';
			echo '
				</div>
		</div>';
		$alternate = !$alternate;
	}

		echo '
				<div class="padding-div"><input type="submit" class="button button_submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
			</div>
		</div>
	</form>';
}

// All the blocks (is this still used?)
function template_latestblocks()
{
	tp_latestblockcodes();
}

// Block Settings Page
function template_blocks()
{
	global $context, $settings, $txt, $scripturl;

	echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tpblocks;sa=updateblocks" method="post">
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="tpadmin_form" value="blocks">
		<div class="cat_bar"><h3 class="category_header">' . $txt['tp-blocksettings'] . '</h3></div>
		<div id="all-the-blocks" class="admintable admin-area">
			<div class="content padding-div">';

		$side   = array('left','right','top','center','front','lower','bottom');
		$sd     = array('lb','rb','tb','cb','fb','lob','bb');

		for($i=0 ; $i<7 ; $i++) {
			echo '
				<div class="font_strong">
					<b>'.$txt['tp-'.$side[$i].'sideblocks'].'</b>
					<a href="'.$scripturl.'?action=admin;area=tpblocks;sa=addblock;side=' . $side[$i] . ';' . $context['session_var'] . '=' . $context['session_id'].'">
					<span style="float: right;"><strong>[' , $txt['tp-addblock'] , ']</strong></span></a>
				</div>';
			if(isset($context['TPortal']['admin' . $side[$i].'panel']) && $context['TPortal']['admin' . $side[$i].'panel']==0 && $side[$i]!='front')
				echo '
				<div class="content">
					<div class="tborder error smalltext" style="padding: 2px;"><a style="color: red;" href="' . $scripturl.'?action=admin;area=tparticles;sa=panels">',$txt['tp-panelclosed'] , '</a></div>
				</div>';

			if(isset($context['TPortal']['admin_'.$side[$i].'block']['blocks'])) {
				$tn=count($context['TPortal']['admin_'.$side[$i].'block']['blocks']);
			}
            else {
				$tn=0;
            }

			if($tn>0) {
				echo '
				<table class="table_grid tp_grid" style="width:100%">
					<thead>
						<tr class="title_bar category_header">
						<th scope="col" class="blocks">
							<div>
								<div style="width:10%;" class="smalltext pos float-items"><strong>'.$txt['tp-pos'].'</strong></div>
								<div style="width:20%;" class="smalltext name float-items"><strong>'.$txt['tp-title'].'</strong></div>
								<div style="width:20%;" class="smalltext title-admin-area float-items" ><strong>'.$txt['tp-type'].'</strong></div>
								<div style="width:10%;" class="smalltext title-admin-area float-items tpcenter"><strong>'.$txt['tp-activate'].'</strong></div>
								<div style="width:20%;" class="smalltext title-admin-area float-items tpcenter"><strong>'.$txt['tp-move'].'</strong></div>
								<div style="width:10%;" class="smalltext title-admin-area float-items tpcenter"><strong>'.$txt['tp-actions'].'</strong></div>
								<div style="width:10%;" class="smalltext title-admin-area float-items tpcenter"><strong>'.$txt['tp-delete'].'</strong></div>
								<p class="clearthefloat"></p>
							</div>
						</th>
						</tr>
					</thead>
					<tbody>';
			}
			else {
				echo '<div class="tp_pad">' .$txt['tp-noblocks']. '</div><br>';
			}
			$n=0;
			if($tn>0) {
				foreach($context['TPortal']['admin_'.$side[$i].'block']['blocks'] as $lblock) {
					$newtitle = TPSubs::getInstance()->getlangOption($lblock['lang'], $context['user']['language']);
					if(empty($newtitle)) {
						$newtitle = $lblock['title'];
                    }

					if(!$lblock['loose']) {
						$class="content3";
					}
                    else {
						$class='content';
					}
					echo '
						<tr class="',$class,'">
						<td class="blocks">
						<div id="blocksDiv">
							<div style="width:10%;" class="adm-pos float-items">
								<input type="number" name="pos' .$lblock['id']. '" value="' .$lblock['pos']. '" style="width: 3em" maxlength="3">
								<a name="block' .$lblock['id']. '"></a>';
					echo '
								<a class="tpbut" title="'.$txt['tp-sortdown'].'" href="' . $scripturl . '?action=admin;area=tpblocks;' . $context['session_var'] . '=' . $context['session_id'].';sa=addpos;id=' .$lblock['id']. '"><img src="' .$settings['tp_images_url']. '/TPsort_down.png" value="' .(($n*10)+11). '" /></a>';

					if($n>0)
						echo '
								<a class="tpbut" title="'.$txt['tp-sortup'].'"  href="' . $scripturl . '?action=admin;area=tpblocks;' . $context['session_var'] . '=' . $context['session_id'].';sa=subpos;id=' .$lblock['id']. '"><img src="' .$settings['tp_images_url']. '/TPsort_up.png" value="' .(($n*10)-11). '" /></a>';

					echo '
							</div>
						<div style="width:20%;max-width:100%;" class="adm-name float-items">
						     <input type="text" name="title' .$lblock['id']. '" value="' .html_entity_decode($newtitle). '" size="25" required>
						</div>
						<div style="width:20%;" class="fullwidth-on-res-layout block-opt float-items">
						    <div class="show-on-responsive">
								<div class="smalltext"><strong>'.$txt['tp-type'].'</strong></div>
							</div>
							<select size="1" name="type' .$lblock['id']. '">
								<option value="0"' ,$lblock['type']=='no' ? ' selected' : '' , '>', $txt['tp-blocktype0'] , '</option>
								<option value="8"' ,$lblock['type']=='shout' ? ' selected' : '' , '>', $txt['tp-blocktype8'] , '</option>
								<option value="18"' ,$lblock['type']=='article' ? ' selected' : '' , '>', $txt['tp-blocktype18'] , '</option>
								<option value="19"' ,$lblock['type']=='category' ? ' selected' : '' , '>', $txt['tp-blocktype19'] , '</option>
								<option value="14"' ,$lblock['type']=='module' ? ' selected' : '' , '>', $txt['tp-blocktype14'] , '</option>
								<option value="5"' ,$lblock['type']=='bbc' ? ' selected' : '' , '>', $txt['tp-blocktype5'] , '</option>
								<option value="11"' ,$lblock['type']=='html' ? ' selected' : '' , '>', $txt['tp-blocktype11'] , '</option>
								<option value="10"' ,$lblock['type']=='php' ? ' selected' : '' , '>', $txt['tp-blocktype10'] , '</option>
								<option value="9"' ,$lblock['type']=='catmenu' ? ' selected' : '' , '>', $txt['tp-blocktype9'] , '</option>
								<option value="2"' ,$lblock['type']=='news' ? ' selected' : '' , '>', $txt['tp-blocktype2'] , '</option>
								<option value="6"' ,$lblock['type']=='online' ? ' selected' : '' , '>', $txt['tp-blocktype6'] , '</option>
								<option value="12"' ,$lblock['type']=='recent' ? ' selected' : '' , '>', $txt['tp-blocktype12'] , '</option>
								<option value="15"' ,$lblock['type']=='rss' ? ' selected' : '' , '>', $txt['tp-blocktype15'] , '</option>
								<option value="4"' ,$lblock['type']=='search' ? ' selected' : '' , '>', $txt['tp-blocktype4'] , '</option>
								<option value="16"' ,$lblock['type']=='sitemap' ? ' selected' : '' , '>', $txt['tp-blocktype16'] , '</option>
								<option value="13"' ,$lblock['type']=='ssi' ? ' selected' : '' , '>', $txt['tp-blocktype13'] , '</option>
								<option value="3"' ,$lblock['type']=='stats' ? ' selected' : '' , '>', $txt['tp-blocktype3'] , '</option>
								<option value="7"' ,$lblock['type']=='theme' ? ' selected' : '' , '>', $txt['tp-blocktype7'] , '</option>
								<option value="1"' ,$lblock['type']=='user' ? ' selected' : '' , '>', $txt['tp-blocktype1'] , '</option>';
				echo '	</select>
						</div>
						<div style="width:10%;" class="smalltext fullwidth-on-res-layout float-items tpcenter">
						    <div class="show-on-responsive"><strong>'.$txt['tp-activate'].'</strong></div>
							&nbsp;<a name="'.$lblock['id'].'"></a>
						    <img class="toggleButton" id="blockonbutton' .$lblock['id']. '" title="'.$txt['tp-activate'].'" src="' .$settings['tp_images_url']. '/TP' , $lblock['off']=='0' ? 'active2' : 'active1' , '.png" alt="'.$txt['tp-activate'].'"  />';
				echo '
						</div>
						<div style="width:20%;" class="smalltext fullwidth-on-res-layout float-items tpcenter">
							<div class="show-on-responsive"><strong>'.$txt['tp-move'].'</strong></div>';

                    foreach( array ( 'blockright', 'blockleft', 'blockcenter', 'blockfront', 'blockbottom', 'blocktop', 'blocklower') as $block_location ) {
                        if($side[$i] != str_replace('block', '', $block_location)) {
							echo '<a href="' . $scripturl . '?action=admin;area=tpblocks;' . $context['session_var'] . '=' . $context['session_id'].';sa='.$block_location.';id=' .$lblock['id']. '"><img title="'.$txt['tp-move'.$block_location].'" src="' .$settings['tp_images_url']. '/TPselect_'.str_replace('block', '', $block_location).'.png" alt="'.$txt['tp-move'.$block_location].'" /></a>';
                        }
                    }

					echo '
						</div>
						<div  style="width:10%;" class="smalltext fullwidth-on-res-layout float-items tpcenter">
						    <div class="show-on-responsive"><strong>'.$txt['tp-editsave'].'</strong></div>
							<a href="' . $scripturl . '?action=admin;area=tpblocks&sa=editblock&id=' .$lblock['id']. ';' . $context['session_var'] . '=' . $context['session_id'].'"><img title="'.$txt['tp-edit'].'" src="' .$settings['tp_images_url']. '/TPconfig_sm.png" alt="'.$txt['tp-edit'].'"  /></a>&nbsp;
							<input type="image" class="tpbut" style="height:16px; vertical-align:top;" src="' .$settings['tp_images_url']. '/TPsave.png" title="'.$txt['tp-send'].'" value="�" onClick="javascript: submit();">
						</div>
	                    <div style="width:10%;" class="smalltext fullwidth-on-res-layout float-items tpcenter">
						    <div class="show-on-responsive"><strong>'.$txt['tp-delete'].'</strong></div>
							<a href="' . $scripturl . '?action=admin;area=tpblocks;' . $context['session_var'] . '=' . $context['session_id'].';sa=blockdelete;id=' .$lblock['id']. '" onclick="javascript:return confirm(\''.$txt['tp-blockconfirmdelete'].'\')"><img title="'.$txt['tp-delete'].'"  src="' .$settings['tp_images_url']. '/TPdelete2.png" alt="'.$txt['tp-delete'].'"  /></a>
						</div>
						<p class="clearthefloat"></p>
					</div>
					</td>
					</tr>';
					if($lblock['type']=='recentbox'){
						// check to see if it is numeric
						if(!is_numeric($lblock['body']))
							$lblock['body']='10';
						echo '
					<tr class="content">
					<td class="blocks">
						<div>
							<div class="padding-div tpcenter">
								'.$txt['tp-numberofrecenttopics'].'<input name="blockbody' .$lblock['id']. '" value="' .$lblock['body']. '" size=4>
							</div>
						</div>
					</td>
					</tr>';
					}
					elseif($lblock['type']=='ssi'){
						// SSI block..which function?
						if(!in_array($lblock['body'],array('recentpoll','toppoll','topposters','topboards','topreplies','topviews','calendar')))
							$lblock['body']='';
						echo '
					<tr class="content">
					<td class="blocks">
						<div>
							<div class="padding-div tpcenter">
								<select name="blockbody' .$lblock['id']. '">
									<option value="" ' , $lblock['body']=='' ? 'selected' : '' , '>' .$txt['tp-none-'].'</option>';
						echo '
									<option value="recentpoll" ' , $lblock['body']=='recentpoll' ? 'selected' : '' , '>'.$txt['tp-ssi-recentpoll'].'</option>';
						echo '
									<option value="toppoll" ' , $lblock['body']=='toppoll' ? 'selected' : '' , '>'.$txt['tp-ssi-toppoll'].'</option>';
						echo '
									<option value="topboards" ' , $lblock['body']=='topboards' ? 'selected' : '' , '>'.$txt['tp-ssi-topboards'].'</option>';
						echo '
									<option value="topposters" ' , $lblock['body']=='topposters' ? 'selected' : '' , '>'.$txt['tp-ssi-topposters'].'</option>';
						echo '
									<option value="topreplies" ' , $lblock['body']=='topreplies' ? 'selected' : '' , '>'.$txt['tp-ssi-topreplies'].'</option>';
						echo '
									<option value="topviews" ' , $lblock['body']=='topviews' ? 'selected' : '' , '>'.$txt['tp-ssi-topviews'].'</option>';
						echo '
									<option value="calendar" ' , $lblock['body']=='calendar' ? 'selected' : '' , '>'.$txt['tp-ssi-calendar'].'</option>
								</select>
							</div>
						</div>
					</td>
					</tr>';
					}
					elseif($lblock['type']=='rss'){
						echo '
					<tr class="content">
					<td class="blocks">
						<div>
							<div class="padding-div tpcenter">
								'.$txt['tp-rssblock'].'<input name="blockbody' .$lblock['id']. '" value="' .$lblock['body']. '" style="width: 75%;">
							</div>
						</div>
					</td>
					</tr>';
					}
					elseif($lblock['type']=='module'){
						echo '
					<tr class="content">
					<td class="blocks">
						<div>
							<div class="padding-div tpcenter">
								<select name="blockbody' .$lblock['id']. '">
									<option value="dl-stats" ' , $lblock['body']=='dl-stats' ? 'selected' : '' , '>' .$txt['tp-module1'].'</option>
									<option value="dl-stats2" ' , $lblock['body']=='dl-stats2' ? 'selected' : '' , '>' .$txt['tp-module2'].'</option>
									<option value="dl-stats3" ' , $lblock['body']=='dl-stats3' ? 'selected' : '' , '>' .$txt['tp-module3'].'</option>
									<option value="dl-stats4" ' , $lblock['body']=='dl-stats4' ? 'selected' : '' , '>' .$txt['tp-module4'].'</option>
									<option value="dl-stats5" ' , $lblock['body']=='dl-stats5' ? 'selected' : '' , '>' .$txt['tp-module5'].'</option>
									<option value="dl-stats6" ' , $lblock['body']=='dl-stats6' ? 'selected' : '' , '>' .$txt['tp-module6'].'</option>
									<option value="dl-stats7" ' , $lblock['body']=='dl-stats7' ? 'selected' : '' , '>' .$txt['tp-module7'].'</option>
									<option value="dl-stats8" ' , $lblock['body']=='dl-stats8' ? 'selected' : '' , '>' .$txt['tp-module8'].'</option>
									<option value="dl-stats9" ' , $lblock['body']=='dl-stats9' ? 'selected' : '' , '>' .$txt['tp-module9'].'</option>
								</select>
							</div>
						</div>
					</td>
					</tr>';
					}
					elseif($lblock['type']=='articlebox'){
						// check to see if it is numeric
						if(!is_numeric($lblock['body']))
							$lblock['body']='';
						echo '
					<tr class="content">
					<td class="blocks">
						<div>
							<div class="padding-div tpcenter">
								<select name="blockbody' .$lblock['id']. '">
								<option value="0">'.$txt['tp-none2'].'</option>';
				foreach($context['TPortal']['edit_articles'] as $article){
					echo '
									<option value="'.$article['id'].'" ' ,$lblock['body']==$article['id'] ? ' selected' : '' ,' >'. html_entity_decode($article['subject'],ENT_QUOTES).'</option>';
				}
						echo '
								</select>
							</div>
						</div>
					</td>
					</tr>';
					}
					elseif($lblock['type']=='categorybox'){
						// check to see if it is numeric
						if(!is_numeric($lblock['body']))
							$lblock['body']='';

						echo '
					<tr class="content">
					<td class="blocks">
						<div>
							<div class="padding-div tpcenter">
								<select name="blockbody' .$lblock['id']. '">
								<option value="0">'.$txt['tp-none2'].'</option>';
					if(isset($context['TPortal']['catnames']) && count($context['TPortal']['catnames'])>0)
					{
						foreach($context['TPortal']['catnames'] as $cat => $val)
						{
							echo '
									<option value="'.$cat.'" ' , $lblock['body']==$cat ? ' selected' : '' ,' >'.html_entity_decode($val).'</option>';
						}
					}
					echo '
								</select>
							</div>
						</div>
					</td>
					</tr>';
					}
					$n++;
				}
			echo '
					</tbody>
				</table><br>';
			}
		}
		echo '
				<div class="padding-div"><input type="submit" class="button button_submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
			</div>
		</div>
	</form>';
}

// Add Block Page
function template_addblock()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $boarddir, $boardurl, $language;

	$side   = isset($_GET['side']) ? $_GET['side'] : '';
	$panels = array('','left','right','top','center','front','lower','bottom');

	echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" enctype="multipart/form-data" action="' . $scripturl . '?action=admin;area=tpblocks;sa=saveblock" method="post">
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="tpadmin_form" value="addblock">
		<div class="cat_bar"><h3 class="category_header">' . $txt['tp-addblock'] . '</h3></div>
		<div id="add-block" class="admintable admin-area">
			<div class="content">
				<div class="formtable padding-div">
					<dl class="tptitle settings">
						<dt><label for="tp_addblocktitle">' , $txt['tp-title'] , '</label>
						</dt>
						<dd>
							<input type="input" name="tp_addblocktitle" id="tp_addblocktitle" value="" size="50" style="max-width:97%;" required>
						</dd>
					</dl>
					<dl class="tptitle settings">
						<dt><label for="field_name">' , $txt['tp-choosepanel'] , '</label></dt>
						<dd>
							<input type="radio" id="tp_addblockpanel1" name="tp_addblockpanel" value="1" ' , $side=='left' ? 'checked' : '' , ' required /><label for="tp_addblockpanel1"">' . $txt['tp-leftpanel'] . '</label><br>
							<input type="radio" id="tp_addblockpanel2" name="tp_addblockpanel" value="2" ' , $side=='right' ? 'checked' : '' , ' /><label for="tp_addblockpanel2"">' . $txt['tp-rightpanel'] . '</label><br>
							<input type="radio" id="tp_addblockpanel6" name="tp_addblockpanel" value="6" ' , $side=='top' ? 'checked' : '' , ' /><label for="tp_addblockpanel6"">' . $txt['tp-toppanel'] . '</label><br>
							<input type="radio" id="tp_addblockpanel3" name="tp_addblockpanel" value="3" ' , $side=='upper' || $side=='center' ? 'checked' : '' , ' /><label for="tp_addblockpanel3"">' . $txt['tp-centerpanel'] . '</label><br>
							<input type="radio" id="tp_addblockpanel4" name="tp_addblockpanel" value="4" ' , $side=='front' ? 'checked' : '' , ' /><label for="tp_addblockpanel4"">' . $txt['tp-frontpanel'] . '</label><br>
							<input type="radio" id="tp_addblockpanel7" name="tp_addblockpanel" value="7" ' , $side=='lower' ? 'checked' : '' , ' /><label for="tp_addblockpanel7"">' . $txt['tp-lowerpanel'] . '</label><br>
							<input type="radio" id="tp_addblockpanel5" name="tp_addblockpanel" value="5" ' , $side=='bottom' ? 'checked' : '' , ' /><label for="tp_addblockpanel5"">' . $txt['tp-bottompanel'] . '</label><br>
						</dd>
					</dl>
					<hr>
					<dl class="tptitle settings">
						<dt><label for="field_name">' , $txt['tp-chooseblock'] , '</label></dt>
						<dd>
							<div class="tp_largelist2">
								<input type="radio" id="tp_addblock18" name="tp_addblock" value="18" checked /><label for="tp_addblock18">' . $txt['tp-blocktype18'] . '</label><br>
								<input type="radio" id="tp_addblock19" name="tp_addblock" value="19" /><label for="tp_addblock19">' . $txt['tp-blocktype19'] . '</label><br>
								<input type="radio" id="tp_addblock14" name="tp_addblock" value="14" /><label for="tp_addblock14">' . $txt['tp-blocktype14'] . '</label><br>
								<input type="radio" id="tp_addblock5" name="tp_addblock" value="5" /><label for="tp_addblock5">' . $txt['tp-blocktype5'] . '</label><br>
								<input type="radio" id="tp_addblock11" name="tp_addblock" value="11" /><label for="tp_addblock11">' . $txt['tp-blocktype11'] . '</label><br>
								<input type="radio" id="tp_addblock10" name="tp_addblock" value="10" /><label for="tp_addblock10">' . $txt['tp-blocktype10'] . '</label><br>
								<input type="radio" id="tp_addblock2" name="tp_addblock" value="2" /><label for="tp_addblock2">' . $txt['tp-blocktype2'] . '</label><br>
								<input type="radio" id="tp_addblock6" name="tp_addblock" value="6" /><label for="tp_addblock6">' . $txt['tp-blocktype6'] . '</label><br>
								<input type="radio" id="tp_addblock12" name="tp_addblock" value="12" /><label for="tp_addblock12">' . $txt['tp-blocktype12'] . '</label><br>
								<input type="radio" id="tp_addblock15" name="tp_addblock" value="15" /><label for="tp_addblock15">' . $txt['tp-blocktype15'] . '</label><br>
								<input type="radio" id="tp_addblock4" name="tp_addblock" value="4" /><label for="tp_addblock4">' . $txt['tp-blocktype4'] . '</label><br>
								<input type="radio" id="tp_addblock13" name="tp_addblock" value="13" /><label for="tp_addblock13">' . $txt['tp-blocktype13'] . '</label><br>
								<input type="radio" id="tp_addblock3" name="tp_addblock" value="3" /><label for="tp_addblock3">' . $txt['tp-blocktype3'] . '</label><br>
								<input type="radio" id="tp_addblock7" name="tp_addblock" value="7" /><label for="tp_addblock7">' . $txt['tp-blocktype7'] . '</label><br>
								<input type="radio" id="tp_addblock1" name="tp_addblock" value="1" /><label for="tp_addblock1">' . $txt['tp-blocktype1'] . '</label><br>
							</div>
						</dd>
					</dl>
					<dl class="tptitle settings">
						<dt><label for="field_name">' , $txt['tp-chooseblocktype'] , '</label></dt>
						<dd>
							<div class="tp_largelist2">';

		foreach($context['TPortal']['blockcodes'] as $bc)
			echo '
						<div class="padding-div">
							<input type="radio" id="tp_addblock' . $bc['name'].'" name="tp_addblock" value="' . $bc['file']. '"  />
							<label for="tp_addblock' . $bc['name'].'"><b>' . $bc['name'].'</b> ' . $txt['tp-by'] . ' ' . $bc['author'] . '</b></label>
							<div style="margin: 4px 0; padding-left: 24px;" class="smalltext">' , $bc['text'] , '</div>
						</div>';

		echo '
							</div>
						</dd>
					</dl>
					<dl class="tptitle settings">
						<dt><label for="field_name">' , $txt['tp-chooseblockcopy'] , '</label></dt>
						<dd>
							<div class="tp_largelist2">';

		foreach($context['TPortal']['copyblocks'] as $bc)
			echo '
						<div class="padding-div">
							<input type="radio" id="tp_addblock_' . $bc['id']. '" name="tp_addblock" value="mb_' . $bc['id']. '"  /><label for="tp_addblock_' . $bc['id']. '">' . $bc['title'].' </label>[' . $panels[$bc['bar']] . ']
						</div>';

		echo ' 				</div>
						</dd>
					</dl>
				</div>
				<div class="padding-div"><input type="submit" class="button button_submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
			</div>
		</div>
	</form>';
}

// Block Access Page
function template_blockoverview()
{
	global $context, $settings, $txt, $boardurl, $scripturl;

	echo '
	<form accept-charset="', 'UTF-8', '" name="tpadmin_news" action="' . $scripturl . '?action=admin;area=tpblocks;sa=updateoverview" method="post">
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="tpadmin_form" value="blockoverview">
		<div class="cat_bar"><h3 class="category_header">' . $txt['tp-blockoverview'] . '</h3></div><div></div>
		<div id="blocks-overview" class="admintable admin-area content">
			<div class="content">';

		$side=array('','left','right','top','center','front','lower','bottom');

		if(allowedTo('tp_blocks') && isset($context['TPortal']['blockoverview']))
		{
			// list by block or by membergroup?
			if(!isset($_GET['grp']))
			{
				foreach($context['TPortal']['blockoverview'] as $block)
				{
					echo '
				<div class="tp_twocolumn">
					<p><a href="' . $scripturl . '?action=admin;area=tpblocks&sa=editblock&id='.$block['id'].';' . $context['session_var'] . '=' . $context['session_id'].'" title="'.$txt['tp-edit'].'"><b>' . $block['title'] . '</b></a> ( ' . $txt['tp-blocktype' . $block['type']] . ' | ' . $txt['tp-' .$side[$block['bar']]] . ')</p>
					<hr>
					<div id="tp'.$block['id'].'" style="overflow: hidden;">
						<input type="hidden" name="' . rand(10000,19999) .'tpblock'.$block['id'].'" value="control" />';

					foreach($context['TPmembergroups'] as $grp)
						echo '
						<input type="checkbox" id="tpb' . $block['id'] . '' . $grp['id'].'" value="' . $grp['id'].'" ' , in_array($grp['id'],$block['access']) ? 'checked="checked" ' : '' , ' name="' . rand(10000,19999) .'tpblock'.$block['id'].'" /><label for="tpb' . $block['id'] . '' . $grp['id'].'"> '. $grp['name'].'</label><br>';

					echo '
					</div>
					<br><input type="checkbox" id="toggletpb'.$block['id'].'" onclick="invertAll(this, this.form, \'tpb'.$block['id'].'\');" /><label for="toggletpb'.$block['id'].'">'.$txt['tp-checkall'],'</label><br><br>
				</div>';
				}
			}
		}
		echo '
			</div>
			<div class="padding-div"><input type="submit" class="button button_submit" name="'.$txt['tp-send'].'" value="'.$txt['tp-send'].'"></div>
		</div>
	</form>';
}
?>
