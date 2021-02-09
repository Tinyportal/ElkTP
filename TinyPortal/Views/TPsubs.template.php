<?php
/**
 * @package TinyPortal
 * @version 1.0.0 RC2
 * @author TinyPortal - http://www.tinyportal.net
 * @license BSD 3.0 http://opensource.org/licenses/BSD-3-Clause/
 *
 * Copyright (C) 2020 - The TinyPortal Team
 *
 */
use TinyPortal\Model\Subs as TPSubs;

// Block template
function TPblock($block, $theme, $side, $double=false)
{
	global $context , $scripturl, $settings, $txt;

	// setup a container that can be massaged through css
	if ($block['type']=='ssi') {
		if ($block['body']=='toptopics') {
			echo '<div class="block_' . $side . 'container" id="ssitoptopics">';
		} elseif ($block['body']=='topboards') {
			echo '<div class="block_' . $side . 'container" id="ssitopboards">';
		} elseif ($block['body']=='topposters') {
			echo '<div class="block_' . $side . 'container" id="ssitopposters">';
		} elseif ($block['body']=='topreplies') {
			echo '<div class="block_' . $side . 'container" id="ssitopreplies">';
		} elseif ($block['body']=='topviews') {
			echo '<div class="block_' . $side . 'container" id="ssitopviews">';
		} elseif ($block['body']=='calendar') {
			echo '<div class="block_' . $side . 'container" id="ssicalendar">';
		} else {
			echo '<div class="block_' . $side . 'container" id="ssiblock">';
		}
	} elseif ($block['type']=='module') {
		if ($block['body']=='dl-stats') {
			echo ' <div class="block_' . $side . 'container" id="module_dl-stats">';
		} elseif ($block['body']=='dl-stats2') {
			echo ' <div class="block_' . $side . 'container" id="module_dl-stats2">';
		} elseif ($block['body']=='dl-stats3') {
			echo ' <div class="block_' . $side . 'container" id="module_dl-stats3">';
		} elseif ($block['body']=='dl-stats4') {
			echo '<div class="block_' . $side . 'container" id="module_dl-stats4">';
		} elseif ($block['body']=='dl-stats5') {
			echo '<div class="block_' . $side . 'container" id="module_dl-stats5">';
		} elseif ($block['body']=='dl-stats6') {
			echo '<div class="block_' . $side . 'container" id="module_dl-stats6">';
		} elseif ($block['body']=='dl-stats7') {
			echo '<div class="block_' . $side . 'container" id="module_dl-stats7">';
		} elseif ($block['body']=='dl-stats8') {
			echo '<div class="block_' . $side . 'container" id="module_dl-stats8">';
		} elseif ($block['body']=='dl-stats9') {
			echo '<div class="block_' . $side . 'container" id="module_dl-stats9">';
		} else {
			echo '<div class="block_' . $side . 'container" id="module_dlstats">';
		}
	} elseif ($block['type']=='shoutbox') {
        //debug_print_backtrace();
	} elseif ($block['type']=='html') {
		echo '<div class="block_' . $side . 'container ' . $block['type'] . 'box" id="htmlbox_' . preg_replace("/[^a-zA-Z]/", "", strip_tags($block['title'])) . '">';
	} elseif ($block['type']=='phpbox') {
		echo '<div class="block_' . $side . 'container ' . $block['type'] . '" id="phpbox_' . preg_replace("/[^a-zA-Z]/", "", strip_tags($block['title'])) . '">';
	} elseif ($block['type']=='scriptbox') {
		echo '<div class="block_' . $side . 'container ' . $block['type'] . '" id="scriptbox_' . preg_replace("/[^a-zA-Z]/", "", strip_tags($block['title'])) . '">';
	} else {
		echo '<div class="block_' . $side . 'container" id="block_' . $block['type'] . '">';
	}

	$types = TPSubs::getInstance()->getBlockStyles();

	// check
	if ( ($block['var5'] == '') || ($block['var5'] == 99) )
		$block['var5'] = $context['TPortal']['panelstyle_'.$side];

	// its a normal block..
	if(in_array($block['frame'],array('theme', 'frame', 'title', 'none'))) {
		echo	'
	<div class="', (($theme || $block['frame'] == 'frame') ? 'tborder tp_'.$side.'block_frame' : 'tp_'.$side.'block_noframe'), '">';

		// show the frame and title
		if ($theme || $block['frame'] == 'title') {
			echo $types[$block['var5']]['code_title_left'];

            if($block['visible'] == '' || $block['visible'] == '1') {
                echo '<a href="javascript:void(0);return%20false" onclick="toggle(\''.$block['id'].'\'); return false"><img id="blockcollapse'.$block['id'].'" style="margin: 2px 0 0 0;float:right" src="' .$settings['tp_images_url']. '/' , !in_array($block['id'],$context['TPortal']['upshrinkblocks'])  ? 'TPcollapse' : 'TPexpand' , '.png" alt="" title="'.$txt['block-upshrink_description'].'" /></a>';
            }

            // can you edit the block?
            if($block['can_manage'] && !$context['TPortal']['blocks_edithide']) {
                echo '<a href="',$scripturl,'?action=admin;area=tpblocks&sa=editblock&id='.$block['id'].';' . $context['session_var'] . '=' . $context['session_id'].'"><img style="margin: 2px 4px 0 0;float:right" src="' .$settings['tp_images_url']. '/TPedit2.png" alt="" title="'.$txt['edit_description'].'" /></a>';
            }

			echo $block['title'];
			echo $types[$block['var5']]['code_title_right'];
		}
		else {
			if(($block['visible'] == '' || $block['visible'] == '1') && $block['frame'] != 'frame') {
				echo '
		<div style="padding: 4px;">';
				if($block['visible'] == '' || $block['visible'] == '1')
					echo '<a href="javascript:void(0);return%20false" onclick="toggle(\''.$block['id'].'\'); return false"><img id="blockcollapse'.$block['id'].'" style="margin: 0;float:right" src="' .$settings['tp_images_url']. '/' , !in_array($block['id'],$context['TPortal']['upshrinkblocks']) ? 'TPcollapse' : 'TPexpand' , '.png" alt="" title="'.$txt['block-upshrink_description'].'" /></a>';
				echo '&nbsp;
		</div>';
			}
		}
		echo '
		<div class="', (($theme || $block['frame'] == 'frame') ? 'tp_'.$side.'block_body' : ''), '"', in_array($block['id'],$context['TPortal']['upshrinkblocks']) ? ' style="display: none;"' : ''  , ' id="block'.$block['id'].'">';
		if($theme || $block['frame'] == 'frame')
			echo $types[$block['var5']]['code_top'];

		$func = 'TPortal_' . $block['type'];
		if (function_exists($func))
		{
			if($double)
			{
				// figure out the height
				$h = $context['TPortal']['blockheight_'.$side];
				if(substr($context['TPortal']['blockheight_'.$side],strlen($context['TPortal']['blockheight_'.$side])-2,2) == 'px')
					$nh = ((substr($context['TPortal']['blockheight_'.$side],0,strlen($context['TPortal']['blockheight_'.$side])-2)*2) + 43).'px';
				elseif(substr($context['TPortal']['blockheight_'.$side],strlen($context['TPortal']['blockheight_'.$side])-1,1) == '%')
					$nh = (substr($context['TPortal']['blockheight_'.$side],0,strlen($context['TPortal']['blockheight_'.$side])-1)*2).'%';
			}
			echo '<div class="blockbody" style="overflow: auto;' , !empty($context['TPortal']['blockheight_'.$side]) ? 'height: '. ($double ? $nh : $context['TPortal']['blockheight_'.$side]) .';' : '' , '">';
			$func($block['id']);
			echo '</div>';
		}
		else
			echo '<div class="blockbody" style="overflow: auto;' , !empty($context['TPortal']['blockheight_'.$side]) ? 'height: '.$context['TPortal']['blockheight_'.$side].';' : '' , '">' , parse_bbc($block['body']) , '</div>';

		if($theme || $block['frame'] == 'frame')
			echo $types[$block['var5']]['code_bottom'];
		echo '
		</div>
	</div>';
	}
	// use a pre-defined layout
	else
	{
		// check if the layout actually exist
		if(!isset($context['TPortal']['blocktheme'][$block['frame']]['body']['before']))
			$context['TPortal']['blocktheme'][$block['frame']] = array(
				'frame' => array('before' => '', 'after' => ''),
				'title' => array('before' => '', 'after' => ''),
				'body' => array('before' => '', 'after' => '')
			);

		echo $context['TPortal']['blocktheme'][$block['frame']]['frame']['before'];
		echo $context['TPortal']['blocktheme'][$block['frame']]['title']['before'];

		// can you edit the block?
		if($block['can_manage'] && !$context['TPortal']['blocks_edithide'])
			echo '<a href="',$scripturl,'?action=admin;area=tpblocks&sa=editblock&id='.$block['id'].';' . $context['session_var'] . '=' . $context['session_id'].'"><img style="margin-right: 4px;float:right" src="' .$settings['tp_images_url']. '/TPedit2.png" alt="" title="'.$txt['edit_description'].'" /></a>';

		echo $block['title'];
		echo $context['TPortal']['blocktheme'][$block['frame']]['title']['after'];
		echo $context['TPortal']['blocktheme'][$block['frame']]['body']['before'];

		$func = 'TPortal_' . $block['type'];
		if (function_exists($func))
			$func();
		else
			echo parse_bbc($block['body']);

		echo $context['TPortal']['blocktheme'][$block['frame']]['body']['after'];
		echo $context['TPortal']['blocktheme'][$block['frame']]['frame']['after'];
	}
	echo '
	</div>';
}

// blocktype 1: User
function TPortal_userbox()
{
	global $context, $settings, $scripturl, $txt, $user_info, $maintenance, $modSettings;

	$bullet = '<img src="'.$settings['tp_images_url'].'/TPdivider.png" alt="" style="margin:0 4px 0 0;" />';
	$bullet2 = '<img src="'.$settings['tp_images_url'].'/TPdivider2.png" alt="" style="margin:0 4px 0 0;" />';
	$bullet3 = '<img src="'.$settings['tp_images_url'].'/TPdivider3.png" alt="" style="margin:0 4px 0 0;" />';
	$bullet4 = '<img src="'.$settings['tp_images_url'].'/TPmodule2.png" alt="" style="margin:0 4px 0 0;" />';
	$bullet5 = '<img src="'.$settings['tp_images_url'].'/TPmodule2.png" alt=""  style="margin:0 4px 0 0;" />';

	echo'
	<div class="tp_userblocknew">';


	// If the user is logged in, display stuff like their name, new messages, etc.

	if ($context['user']['is_logged']) {

		if (!empty($context['user']['avatar']) &&  isset($context['TPortal']['userbox']['avatar'])) {
			echo '
				<span class="tpavatar">', $context['user']['avatar']['image'], '</span>';
        }

		echo '
		<strong><a class="subject"  href="'.$scripturl.'?action=profile;u='.$context['user']['id'].'">', $context['user']['name'], '</a></strong>
		<ul class="reset">';

		// Only tell them about their messages if they can read their messages!
		if ($context['allow_pm']) {
			echo '
			<li><a href="', $scripturl, '?action=pm">' .$bullet.$txt['tp-pm'].' ',  $context['user']['messages'], '</a></li>';
			if($context['user']['unread_messages'] > 0)
				echo '
			<li style="font-weight: bold; "><a href="', $scripturl, '?action=pm">' . $bullet. $txt['tp-pm2'].' ',$context['user']['unread_messages'] , '</a></li>';
		}
		// Are there any members waiting for approval?
		if (!empty($context['unapproved_members'])) {
			echo '<li><a href="', $scripturl, '?action=admin;area=viewmembers;sa=browse;type=approve;' . $context['session_var'] . '=' . $context['session_id'].'">'. $bullet. $txt['tp_unapproved_members'].' '. $context['unapproved_members']  . '</a></li>';
        }
		// Are there any moderation reports?
		if (!empty($context['open_mod_reports']) && $context['show_open_reports']) {
			echo '
				<li><a href="', $scripturl, '?action=moderate;area=reports">'.$bullet.$txt['tp_modreports'].' ' . $context['open_mod_reports']. '</a></li>';
		}

		if(isset($context['TPortal']['userbox']['unread'])) {
			echo '<li><hr><a href="', $scripturl, '?action=unread">' .$bullet.$txt['tp-unread'].'</a></li>
			<li><a href="', $scripturl, '?action=unreadreplies">'.$bullet.$txt['tp-replies'].'</a></li>
			<li><a href="', $scripturl, '?action=profile;u='.$context['user']['id'].';area=showposts">'.$bullet.$txt['tp-showownposts'].'</a></li>
			<li><a href="', $scripturl, '?action=tparticle;sa=showcomments">'.$bullet.$txt['tp-showcomments'].'</a><hr></li>
			';
        }

		// Is the forum in maintenance mode?
		if ($maintenance && $context['user']['is_admin']) {
			echo '<li>' .$bullet2.$txt['tp_maintenace']. '</li>';
        }
		// Show the total time logged in?
		if (!empty($context['user']['total_time_logged_in']) && isset($context['TPortal']['userbox']['logged'])) {
			echo '<li>' .$bullet2.$txt['tp-loggedintime'] . '</li>
			<li>'.$bullet2.$context['user']['total_time_logged_in']['days'] . $txt['tp-acronymdays']. $context['user']['total_time_logged_in']['hours'] . $txt['tp-acronymhours']. $context['user']['total_time_logged_in']['minutes'] .$txt['tp-acronymminutes'].'</li>';
		}
		if (isset($context['TPortal']['userbox']['time'])) {
    		echo '<li>' . $bullet2.$context['current_time'].' <hr></li>';
        }

		// admin parts etc.
         if(!isset($context['TPortal']['can_submit_article'])) {
            $context['TPortal']['can_submit_article']=0;
        }

         // can we submit an article?
         if(allowedTo('tp_submithtml')) {
             echo '<li><a href="', $scripturl, '?action=admin;area=tparticles;sa=addarticle_html">' . $bullet3.$txt['tp-submitarticle']. '</a></li>';
         }

       	if(allowedTo('tp_submitbbc')) {
			echo '<li><a href="', $scripturl, '?action=admin;area=tparticles;sa=addarticle_bbc">' . $bullet3.$txt['tp-submitarticlebbc']. '</a></li>';
        }

		if(allowedTo('tp_editownarticle')) {
			echo '<li><a href="', $scripturl, '?action=admin;area=tparticles;sa=myarticles">' . $bullet3.$txt['tp-myarticles']. '</a></li>';
        }

		// tpadmin checks
		if (allowedTo('tp_settings')) {
			echo '<li><hr><a href="' . $scripturl . '?action=admin;area=tparticles;sa=settings">' . $bullet4.$txt['permissionname_tp_settings'] . '</a></li>';
        }
		
        if (allowedTo('tp_blocks')) {
			echo '<li><a href="' . $scripturl . '?action=admin;area=tparticles;sa=blocks">' . $bullet4.$txt['permissionname_tp_blocks'] . '</a></li>';
        }

		if (allowedTo('tp_articles')) {
			echo '<li><a href="' . $scripturl . '?action=admin;area=tparticles;sa=articles">' . $bullet4.$txt['permissionname_tp_articles'] . '</a></li>';
		}
				echo '
		</ul>';
	}
	// Otherwise they're a guest - so politely ask them to register or login.
    else  {
        if (!empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1) {
            $txt['welcome_guest'] .= $txt['welcome_guest_activate'];
        }

        $txt['welcome_guest'] = replaceBasicActionUrl($txt['welcome_guest']);

        echo '<div style="line-height: 1.4em;">', $txt['welcome_guest'], '</div>';
    echo '
        <form style="margin-top: 5px;" action="', $scripturl, '?action=login2" method="post" >
            <input type="text" class="input_text" name="user" size="10" style="max-width: 45%!important;"/> <input type="password" class="input_password" name="passwrd" size="10" style="max-width: 45%!important;"/><br>
            <select name="cookielength" style="max-width: 45%!important;">
                <option value="60">', $txt['one_hour'], '</option>
                <option value="1440">', $txt['one_day'], '</option>
                <option value="10080">', $txt['one_week'], '</option>
                <option value="302400">', $txt['one_month'], '</option>
                <option value="-1" selected="selected">', $txt['forever'], '</option>
            </select>
            <input type="submit" class="button_submit" value="', $txt['login'], '" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="', $context['login_token_var'], '" value="', $context['login_token'], '">
        </form>
        <div style="line-height: 1.4em;" class="middletext">', $txt['quick_login_dec'], '</div>';
	}
	echo '
	</div>';
}

// blocktype 2: News
function TPortal_newsbox()
{
	global $context;

	// Show a random news item? (or you could pick one from news_lines...)
	echo '<div class="tp_newsblock">', $context['random_news_line'], '</div>';
}

// blocktype 3: Stats
function TPortal_statsbox()
{
	global $context, $settings, $scripturl, $txt, $modSettings;

	$bullet = '<img src="'.$settings['tp_images_url'].'/TPdivider.png" alt=""  style="margin:0 4px 0 0;" />';
	$bullet2 = '<img src="'.$settings['tp_images_url'].'/TPdivider2.png" alt="" style="margin:0 4px 0 0;" />';

	echo'
	<div class="tp_statsblock">';

	if(isset($context['TPortal']['userbox']['stats']))
		// members stats
		echo '
		<h5 class="mlist"><a href="'.$scripturl.'?action=memberlist">'.$txt['members'].'</a></h5>
		<ul>
			<li>' . $bullet. $txt['total_members'].': ' , isset($modSettings['memberCount']) ? $modSettings['memberCount'] : $modSettings['totalMembers'] , '</li>
			<li>' . $bullet. $txt['tp-latest']. ': <a href="', $scripturl, '?action=profile;u=', $modSettings['latestMember'], '"><strong>', $modSettings['latestRealName'], '</strong></a></li>
		</ul>';
	if(isset($context['TPortal']['userbox']['stats_all']))
		// more stats
		echo '
		<h5 class="stats"><a href="'.$scripturl.'?action=stats">'.$txt['tp-stats'].'</a></h5>
		<ul>
			<li>'.  $bullet. $txt['total_posts'].': '.$modSettings['totalMessages']. '</li>
			<li>'.  $bullet. $txt['total_topics'].': '.$modSettings['totalTopics']. '</li>
			<li>' . $bullet. $txt['tp-mostonline-today'].': '.$modSettings['mostOnlineToday'].'</li>
			<li>' . $bullet. $txt['tp-mostonline'].': '.$modSettings['mostOnline'].'</li>
			<li>('.standardTime($modSettings['mostDate']).')</li>
		</ul>';

	if(isset($context['TPortal']['userbox']['online']))
	{
		// add online users
		echo '
		<h5 class="online"><a href="'.$scripturl.'?action=who">'.$txt['online_users'].'</a></h5>
		<div class="tp_stats_users" style="line-height: 1.3em;">';

		$online = ssi_whosOnline('array');
		echo  $bullet. $txt['tp-users'].': '.$online['num_users']. '<br>
			'. $bullet. $txt['tp-guests'].': '.$online['guests'].'<br>
			'. $bullet. $txt['tp-total'].': '.$online['total_users'].'<br>
			<div style="max-height: 23em; overflow: auto;">';

		foreach($online['users'] as $user)
		{
			echo  $bullet2 , $user['hidden'] ? '<i>' . $user['link'] . '</i>' : $user['link'];
			echo '<br>';
		}
		echo '
			</div></div>';
	}
	echo '
	</div>';
}

// blocktype 4: search
function TPortal_searchbox()
{
	global $context, $txt, $scripturl;

	echo '
	<form accept-charset="', 'UTF-8', '" action="', $scripturl, '?action=search;sa=results" method="post" style="padding: 0; text-align: center; margin: 0; ">
		<input type="text" class="block_search" name="search" value="" />
		<input type="submit" name="submit" value="', $txt['search'], '" class="block_search_submit button_submit" /><br>
		<br><span class="smalltext"><a href="', $scripturl, '?action=search;advanced">', $txt['search_advanced'], '</a></span>
		<input type="hidden" name="advanced" value="0" />
	</form>';
}

// blocktype 6: online
function TPortal_onlinebox()
{
	global $context;

	if($context['TPortal']['useavataronline'] == 1)
		tpo_whos();
	else
		echo '
	<div style="line-height: 1.4em;">' , ssi_whosOnline() , '</div>';
}

// blocktype 7: Themes
function TPortal_themebox()
{
	global $context, $settings, $scripturl, $txt;

	$what = explode(',', $context['TPortal']['themeboxbody']);
	$temaid = array();
	$temanavn = array();
	$temapaths = array();
	foreach($what as $wh => $wht)
	{
		$all = explode('|', $wht);
		if($all[0] > -1)
		{
			$temaid[] = $all[0];
			$temanavn[] = isset($all[1]) ? $all[1] : '';
			$temapaths[] = isset($all[2]) ? $all[2] : '';
		}
	}

	if(isset($context['TPortal']['querystring']))
		$tp_where = Util::htmlspecialchars(strip_tags($context['TPortal']['querystring']));
	else
		$tp_where = 'action=forum';

	if($tp_where != '')
		$tp_where .= ';';

	// remove multiple theme=x in the string.
	$tp_where=preg_replace("'theme=[^>]*?;'si", "", $tp_where);

	 if(is_countable($temaid) && count($temaid) > 0){
        echo '
		<form name="jumpurl1" onsubmit="return jumpit()" class="middletext" action="" style="padding: 0; margin: 0; text-align: center;">
			<select style="width: 100%; margin: 5px 0px 5px 0px;" size="1" name="jumpurl2" onchange="check(this.value)">';
         for($a=0 ; $a<(count($temaid)); $a++)
		 {
                echo '
				<option value="'.$temaid[$a].'" ', $settings['theme_id'] == $temaid[$a] ? 'selected="selected"' : '' ,'>'.substr($temanavn[$a],0,20).'</option>';
         }
		 echo '
			</select><br>' , $context['user']['is_logged'] ?
			'<input type="checkbox" value=";permanent" onclick="realtheme()" /> '. $txt['tp-permanent']. '<br>' : '' , '<br>
			<input type="button" class="button_submit" value="'.$txt['tp-changetheme'].'" onclick="jumpit()" /><br><br>
			<input type="hidden" value="'.Util::htmlspecialchars($scripturl . '?'.$tp_where.'theme='.$settings['theme_id']).'" name="jumpurl3" />
			<div style="text-align: center; width: 95%; overflow: hidden;">';

			echo ' <img src="'.$settings['images_url'].'/thumbnail.png" alt="" id="chosen" name="chosen" style="max-width: 100%;" />';

		echo '
			</div>
		</form>
		<script type="text/javascript"><!-- // --><![CDATA[
			var extra = \'\';
			var themepath = new Array();';
		 for($a=0 ; $a<(count($temaid)); $a++){
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
				document.jumpurl1.jumpurl3.value = \'' . $scripturl . '?'. $tp_where.'theme=\' + icon
			}
		// ]]></script>';
	}
	else
		echo $txt['tp-nothemeschosen'];
}

// blocktype 8: TP Shoutbox
function TPortal_shoutbox($blockid)
{
	global $context;

	// fetch the correct block
	if(!empty($context['TPortal']['moduleid'])) {
		$tpm                = $context['TPortal']['moduleid'];
        $shoutbox_id        = 0;
        $shoutbox_layout    = null;
		if(!empty($context['TPortal']['tpblocks']['blockrender'][$tpm]['function']) && function_exists($context['TPortal']['tpblocks']['blockrender'][$tpm]['function'])) {
            if(isset($context['TPortal']['tpblocks']['blockrender'][$tpm]['shoutbox_id'])) {
                $shoutbox_id = $context['TPortal']['tpblocks']['blockrender'][$tpm]['shoutbox_id'];
            }
            if(isset($context['TPortal']['tpblocks']['blockrender'][$tpm]['shoutbox_layout'])) {
                $shoutbox_layout = $context['TPortal']['tpblocks']['blockrender'][$tpm]['shoutbox_layout'];
            }

			call_user_func($context['TPortal']['tpblocks']['blockrender'][$tpm]['function'], $shoutbox_id, $shoutbox_layout);
        }
	}
}

// blocktype 9: Menu
function TPortal_catmenu()
{
	global $context, $scripturl, $boardurl, $settings;

	if(isset($context['TPortal']['menu'][$context['TPortal']['menuid']]) && !empty($context['TPortal']['menu'][$context['TPortal']['menuid']])){
		echo '
	<ul class="tp_catmenu">';

		foreach($context['TPortal']['menu'][$context['TPortal']['menuid']] as $cn)
		{
			echo '
		<li', $cn['type']=='head' ? ' class="tp_catmenu_header"' : '' ,'>';
			if($context['TPortal']['menuvar1'] == '' || $context['TPortal']['menuvar1'] == '0')
				echo str_repeat("&nbsp;&nbsp;", ($cn['sub'] + 1));
			elseif($context['TPortal']['menuvar1'] == '1')
				echo str_repeat("&nbsp;&nbsp;", ($cn['sub'] + 1));
			elseif($context['TPortal']['menuvar1'] == '2')
				echo str_repeat("&nbsp;&nbsp;", ($cn['sub'] + 1));

			if((!isset($cn['icon']) || (isset($cn['icon']) && $cn['icon'] == '')) && $cn['type'] != 'head' && $cn['type'] != 'spac')
			{
				if($context['TPortal']['menuvar1'] == '' || $context['TPortal']['menuvar1'] == '0')
					echo '
			<img src="'.$settings['tp_images_url'].'/TPdivider2.png" alt="" />&nbsp;';
				elseif($context['TPortal']['menuvar1'] == '1')
					echo '
			<img src="'.$settings['tp_images_url'].'/bullet3.png" alt="" />';

			}
			elseif(isset($cn['icon']) && $cn['icon'] != '' && $cn['type'] != 'head' && $cn['type'] != 'spac')
			{
				echo '
			<img alt="*" src="'.$cn['icon'].'" />&nbsp;';
			}
			switch($cn['type'])
			{
				case 'cats' :
					echo '
			<a href="'. $scripturl. '?cat='.$cn['IDtype'].'"' .( $cn['newlink']=='1' ? ' target="_blank"' : ''). '>'.$cn['name'].'</a>';
					break;
				case 'arti' :
					echo '
			<a href="'. $scripturl. '?page='.$cn['IDtype'].'"' .($cn['newlink']=='1' ? ' target="_blank"' : '') . '>'.$cn['name'].'</a>';
					break;
				case 'link' :
					echo '
			<a href="'.$cn['IDtype'].'"' . ($cn['newlink']=='1' ? ' target="_blank"' : '') . '>'.$cn['name'].'</a>';
					break;
				case 'head' :
					echo '
			<a class="tp_catmenu_header" name="header'.$cn['id'].'"><b>'.$cn['IDtype'].'</b></a>';
					break;
				case 'spac' :
					echo '
			<a name="spacer'.$cn['id'].'">&nbsp;</a>';
					break;
				default :
					echo '
			<a href="'.$cn['IDtype'].'"' . ($cn['newlink']=='1' ? ' target="_blank"' : '') . '>'.$cn['name'].'</a>';
					break;
			}
			echo '</li>';
		}
		echo '
	</ul>';
	}
}

// blocktype 10: PHP code
function TPortal_phpbox()
{
	global $context;

	// execute what is in the block, no echoing
	if(!empty($context['TPortal']['phpboxbody']));
		eval(tp_convertphp($context['TPortal']['phpboxbody'],true));
}

// blocktype 11: HTML & Javascript code
function TPortal_scriptbox()
{
	global $context;

    echo $context['TPortal']['scriptboxbody'];
}

// blocktype 12: Recent Topics
function TPortal_recentbox()
{
	global $scripturl, $context, $settings, $txt, $modSettings;

	// is it a number?
	if(!is_numeric($context['TPortal']['recentboxnum']))
		$context['TPortal']['recentboxnum']='10';

	// exclude boards
	if (isset($context['TPortal']['recentboards']) && $context['TPortal']['boardmode'] == 0)
		$exclude_boards = $context['TPortal']['recentboards'];
	else {
	// leave out the recycle board, if any
		if(isset($modSettings['recycle_board']) && $modSettings['recycle_enable'] = 1 )
		$bb = array($modSettings['recycle_board']);
		$exclude_boards = $bb;
	}

	// include boards
	if (isset($context['TPortal']['recentboards']) && !$context['TPortal']['boardmode'] == 0)
		$include_boards = $context['TPortal']['recentboards'];
	else
		$include_boards = null;

	$what = ssi_recentTopics($num_recent = $context['TPortal']['recentboxnum'] , $exclude_boards,  $include_boards, $output_method = 'array');
	if($context['TPortal']['useavatar'] == 0)
	{
		// Output the topics
		echo '
		<ul class="recent_topics" style="' , isset($context['TPortal']['recentboxscroll']) && $context['TPortal']['recentboxscroll'] == 1 ? 'overflow: auto; height: 20ex;' : '' , 'margin: 0; padding: 0;">';
		$coun = 1;
		foreach($what as $wi => $w)
		{
			echo '
			<li' , $coun<count($what) ? '' : ' style="border: none; margin-bottom: 0;padding-bottom: 0;"'  , '>';
				echo ' <a href="' . $scripturl . '?topic=' . $w['topic'] . '.msg' . $w['new_from'] . ';topicseen#new" rel="nofollow" class="new_posts" style="margin:0px;">' . $txt['new'] . '</a> ';
			echo '
				<a href="' . $w['href'] . '" title="' . $w['subject'] . '">' . $w['short_subject'] . '</a>
				 ', $txt['by'], ' <b>', $w['poster']['link'],'</b> ';
			echo '<br><span class="smalltext">['.$w['time'].']</span>
			</li>';
			$coun++;
		}
		echo '
		</ul>';
	}
	else
	{
		$member_ids = array();
		foreach($what as $wi => $w)
		{
			$member_ids[] = $w['poster']['id'];
		}

		if(!empty($member_ids))
			$avatars = progetAvatars($member_ids);
		else
			$avatars = array();

		// Output the topics
		$coun = 1;
		echo '
		<ul class="recent_topics" style="' , isset($context['TPortal']['recentboxscroll']) && $context['TPortal']['recentboxscroll']==1 ? 'overflow: auto; height: 20ex;' : '' , 'margin: 0; padding: 0;">';
		foreach($what as $wi => $w)
		{
			echo '
			<li' , $coun<count($what) ? '' : ' style="border: none; margin-bottom: 0;padding-bottom: 0;"'  , '>';
				echo ' <a href="' . $scripturl . '?topic=' . $w['topic'] . '.msg' . $w['new_from'] . ';topicseen#new" rel="nofollow" class="new_posts" style="margin:0px;">' . $txt['new'] . '</a> ';
			echo '
					<span class="tpavatar"><a href="' . $scripturl. '?action=profile;u=' . $w['poster']['id'] . '">' , empty($avatars[$w['poster']['id']]) ? '<img src="' . $settings['tp_images_url'] . '/TPguest.png" alt="" />' : $avatars[$w['poster']['id']] , '</a></span><a href="'.$w['href'].'">' . $w['short_subject'].'</a>
				 ', $txt['by'], ' <b>', $w['poster']['link'],'</b> ';
			echo '<br><span class="smalltext">['.$w['time'].']</span>
			</li>';
			$coun++;
		}
		echo '
		</ul>';
	}
}
// blocktype 13: SSI functions
function TPortal_ssi()
{
	global $context;
	echo '<div style="padding: 5px;" class="smalltext">';
	if($context['TPortal']['ssifunction'] == 'toptopics')
		ssi_topTopics();
	elseif($context['TPortal']['ssifunction'] == 'topboards')
		ssi_topBoards();
	elseif($context['TPortal']['ssifunction'] == 'topposters')
		ssi_topPoster(5);
	elseif($context['TPortal']['ssifunction'] == 'topreplies')
		ssi_topTopicsReplies();
	elseif($context['TPortal']['ssifunction'] == 'topviews')
		ssi_topTopicsViews();
	elseif($context['TPortal']['ssifunction'] == 'calendar')
		ssi_todaysCalendar();

	echo '</div>';
}

// blocktype 14: Article / Download functions
function TPortal_module()
{
   global $context, $scripturl, $txt;

	switch($context['TPortal']['moduleblock'])
	{
		case 'dl-stats':
			dl_recentitems('8', 'date', 'echo');
			break;
		case 'dl-stats2':
			dl_recentitems('8', 'downloads', 'echo');
			break;
		case 'dl-stats3':
			dl_recentitems('8', 'views', 'echo');
			break;
		case 'dl-stats4':
			$it = array();
			$it = dl_recentitems('1', 'date', 'array');
			if(is_countable($it) && count($it) > 0)
			{
				foreach($it as $item)
				{
					echo '
					<img src="'.$item['icon'].'" style="margin-left: 4px;float:right" alt="" />
						<a href="'.$item['href'].'"><b>'.$item['name'].'</b></a>
						<p class="smalltext">'.$txt['tp-uploadedby'].' <b>'.$item['author'].'</b> <br>( '.$item['date'].')<br>
						'.$txt['tp-downloads'].'/'.$txt['tp-itemviews'].': <b>'.$item['downloads'].' / '.$item['views'].'</b></p>';
				}
			}
			break;
		case 'dl-stats5':
			$it = array();
			$it = dl_recentitems('1', 'downloads', 'array');
			if(is_countable($it) && count($it) > 0)
			{
				foreach($it as $item)
				{
					echo '
					<img src="'.$item['icon'].'" style="margin-left: 4px;float:right" alt="" />
						<a href="'.$item['href'].'"><b>'.$item['name'].'</b></a>
						<p class="smalltext">'.$txt['tp-uploadedby'].' <b>'.$item['author'].'</b> <br>( '.$item['date'].')<br>
						'.$txt['tp-downloads'].'/'.$txt['tp-itemviews'].': <b>'.$item['downloads'].' / '.$item['views'].'</b></p>';
				}
			}
			break;
		case 'dl-stats6':
			$it = array();
			$it = dl_recentitems('1', 'views', 'array');
			if(is_countable($it) && count($it) > 0)
			{
				foreach($it as $item)
				{
					echo '
					<img src="'.$item['icon'].'" style="margin-left: 4px;float:right" alt="" />
						<a href="'.$item['href'].'"><b>'.$item['name'].'</b></a>
						<p class="smalltext">'.$txt['tp-uploadedby'].' <b>'.$item['author'].'</b> <br>( '.$item['date'].')<br>
						'.$txt['tp-downloads'].'/'.$txt['tp-itemviews'].': <b>'.$item['downloads'].' / '.$item['views'].'</b></p>';
				}
			}
			break;
		case 'dl-stats7':
			$it = array();
			$it = art_recentitems('5','date');
			if(is_countable($it) && count($it) > 0)
			{
				foreach($it as $item)
				{
					echo '<span class="smalltext"><a title="'.$item['date'].'" href="'.$scripturl.'?page='.$item['id'].'">'.$item['subject'].'</a>
						</span><br>';
				}
			}
			break;
		case 'dl-stats8':
			$it = array();
			$it = art_recentitems('5', 'views');
			if(is_countable($it) && count($it) > 0)
			{
				foreach($it as $item)
				{
					echo '<span class="smalltext"><a title="'.$txt['tp-views'].': '.$item['views'].'" href="'.$scripturl.'?page='.$item['id'].'">'.$item['subject'].'</a>
						</span><br>';
				}
			}
			break;
		case 'dl-stats9':
			$it = array();
			$it = art_recentitems('5', 'comments');
			if(is_countable($it) && count($it) > 0)
			{
				foreach($it as $item)
				{
					echo '<span class="smalltext"><a title="'.$item['comments'].'" href="'.$scripturl.'?page='.$item['id'].'">'.$item['subject'].'</a>
						('.$item['comments'].')<br></span>';
				}
			}
				break;
     }
}

// blocktype 15: RSS block
function TPortal_rss()
{
	global $context;

	echo '<div style="padding: 5px; ' , !empty($context['TPortal']['rsswidth']) ? 'max-width: ' . $context['TPortal']['rsswidth'] .';' : '' , '" class="middletext">' , TPSubs::getInstance()->parseRSS('', $context['TPortal']['rss_utf8']) , '</div>';
}

// blocktype 16: sitemap
function TPortal_sitemap()
{
    global $context, $settings, $scripturl, $txt;

	$current = '';
    // check where we are
    if(isset($_GET['action']) && $_GET['action'] == 'tpmod')
	{
		if(isset($_GET['dl']))
			$current = 'dl';
		elseif(isset($_GET['link']))
			$current = 'link';
		elseif(isset($_GET['show']))
			$current = 'show';
		elseif(isset($_GET['team']))
			$current = 'team';
		else
			$current = '';
    }
         echo '
	<div class="tborder">
		<ul class="tpsitemap">';

	if(!empty($context['TPortal']['sitemap']) && !empty($context['TPortal']['menu']))
	{
		foreach($context['TPortal']['menu'] as $main)
		{
			foreach($main as $cn)
			{
				// check if we can find the link on current tpage
				$catclass = '';
				if($cn['type'] == 'cats')
				{
					if(isset($_GET['cat']) && $cn['IDtype'] == $_GET['cat'])
						$catclass = 'tpsitemapheader';
				}
				elseif($cn['type'] == 'arti'){
					if(isset($_GET['page']) && $cn['IDtype'] == $_GET['page'])
						$catclass = 'tpsitemapheader';
				}
				elseif($cn['type'] == 'link'){
					if(!empty($context['TPortal']['querystring']))
						$qs = $scripturl.'?'.$context['TPortal']['querystring'];
					else
						$qs = $scripturl;

					if($qs == $cn['IDtype'])
						$catclass = 'tpsitemapheader';
				}

				if($cn['sitemap'] == '1'){
					switch($cn['type']){
							case 'cats' :
								echo '<li><a class="' , $catclass ,'" href="'. $scripturl. '?cat='.$cn['IDtype'].'" ' , $cn['newlink']=='1' ? 'target="_blank"' : '' , '><img src="' .$settings['tp_images_url']. '/TPdivider.png" alt="" /> '.$cn['name'].'</a></li>';
								break;
							case 'arti' :
								echo '<li><a class="' , $catclass ,'" href="'. $scripturl. '?page='.$cn['IDtype'].'"' , $cn['newlink']=='1' ? 'target="_blank"' : '' , '><img src="' .$settings['tp_images_url']. '/TPdivider.png" alt="" /> '.$cn['name'].'</a></li>';
								break;
							case 'link' :
								echo '<li><a class="' , $catclass ,'" href="'.$cn['IDtype'].'"' , $cn['newlink']=='1' ? 'target="_blank"' : '' , '><img src="' .$settings['tp_images_url']. '/TPdivider.png" alt="" /> '.$cn['name'].'</a></li>';
								break;
					}
				}
			}
		}
	}
	echo '
		</ul>
	</div>';
}

// blocktype 18: Single Article
function TPortal_articlebox()
{
	global $context;

	if(isset($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]))
		echo '<div class="block_article">', 	template_blockarticle() , '</div>';
}

// blocktype 19: Articles in a Category
function TPortal_categorybox()
{
    global $context, $txt, $scripturl;

	if(isset($context['TPortal']['blockarticle_titles'][$context['TPortal']['blocklisting']])){
		echo '<div class="middletext" ', (count($context['TPortal']['blockarticle_titles'][$context['TPortal']['blocklisting']])>$context['TPortal']['blocklisting_height'] && $context['TPortal']['blocklisting_height']!='0') ? ' style="overflow: auto; width: 100%; height: '.$context['TPortal']['blocklisting_height'].'em;"' : '' ,'>';
		foreach($context['TPortal']['blockarticle_titles'][$context['TPortal']['blocklisting']] as $listing){
			if($listing['category'] == $context['TPortal']['blocklisting'])
				echo '<b><a href="'.$scripturl.'?page='.$listing['shortname'].'">'.$listing['subject'].'</a></b> ' , $context['TPortal']['blocklisting_author']=='1' ? $txt['by'].' '.$listing['poster'] : '' , '<br>';
		}
		echo '</div>';
	}
 }

// dummy for old templates
function TPortal_sidebar()
{
	return;
}

// Some functions, not templates
function tpo_whos($buddy_only = false)
{
	global $txt, $scripturl;

	$whos = tpo_whosOnline();
	echo '
	<div>
	' . $whos['num_guests'] .' ' , $whos['num_guests'] == 1 ? $txt['guest'] : $txt['guests'] , ',
	' . $whos['num_users_online'] .' ' , $whos['num_users_online'] == 1 ? $txt['user'] : $txt['users'] , '
	</div>';
	if(isset($whos['users_online']) && count($whos['users_online']) > 0)
	{
		$ids = array();
		$names = array();
		$times = array();
		foreach($whos['users_online'] as $w => $wh)
		{
			// For reasons historical, ELK produces the timestamp as
			// the timestamp followed by the user's name, so let's fix it.
			$timestamp = (int) strtr($w, array($wh['username'] => ''));
			$ids[] = $wh['id'];
			$names[$wh['id']] = $wh['name'];
			$times[$wh['id']] = standardTime($timestamp);
		}
		$avy = progetAvatars($ids);
		foreach($avy as $a => $av)
			echo '
		<a class="tp_avatar_single2" title="'.$names[$a].'" href="' . $scripturl . '?action=profile;u='.$a.'">' . $av . '</a>';
	}
}

function tpo_whosOnline()
{
    require_once(SUBSDIR . '/MembersOnline.subs.php');
	$membersOnlineOptions = array(
		'show_hidden' => allowedTo('moderate_forum'),
		'sort' => 'log_time',
		'reverse_sort' => true,
	);
	$return = getMembersOnlineStats($membersOnlineOptions);
	return $return;
}

function progetAvatars($ids)
{
	global $user_info, $modSettings, $scripturl;
	global $image_proxy_enabled, $image_proxy_secret, $boardurl;

    $db = database();

	$request = $db->query('', '
		SELECT
			mem.real_name, mem.member_name, mem.id_member, mem.show_online,mem.avatar, mem.email_address AS email_address,
			COALESCE(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type AS attachment_type
		FROM {db_prefix}members AS mem
		LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member AND a.attachment_type != 3)
		WHERE mem.id_member IN ({array_int:ids})',
		array('ids' => $ids)
	);

	$avy = array();
	if($db->num_rows($request) > 0)
	{
		while ($row = $db->fetch_assoc($request)) {
            $avy[$row['id_member']] = determineAvatar( array(
                    'avatar'            => $row['avatar'],
                    'email_address'     => $row['email_address'],
                    'filename'          => !empty($row['filename']) ? $row['filename'] : '',
                    'id_attach'         => $row['id_attach'],
                    'attachment_type'   => $row['attachment_type'],
                )
            )['image'];
		}
		$db->free_result($request);
	}

	return $avy;
}

// a dummy layer for layer articles
function template_nolayer_above()
{
	global $context;

	echo '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
		<meta name="keywords" content="' . $context['meta_keywords'] . '" />
		<title>' , $context['page_title'] , '</title>
		' , $context['tp_html_headers'] , '
	</head>
	<body><div id="nolayer_frame">';
}

function template_nolayer_below()
{
	echo '<small id="nolayer_copyright">',theme_copyright(),'</small>
	</div></body></html>';
}

// article search page 1
function template_TPsearch_above()
{
	global $context, $txt, $scripturl;

    echo '
	<div style="padding: 0 5px;">
		<div class="cat_bar">
			<h3 class="category_header">' , $txt['tp-searcharticles'] , '</h3>
		</div>
		<div class="content">
			<span class="topslice"><span></span></span>
			<p style="margin: 0; padding: 0 1em;">
				<a href="' . $scripturl. '?action=tpsearch;sa=searcharticle">' . $txt['tp-searcharticles2'] . '</a>';

	// any others?
	if(!empty($context['TPortal']['searcharray']) && count($context['TPortal']['searcharray']) > 0) {
		echo implode(' | ', $context['TPortal']['searcharray']);
    }

	echo '
			</p>
			<span class="botslice"><span></span></span>
		</div>
	</div>';

}

function template_TPsearch_below()
{
	return;
}

// Error page
function template_tperror_above()
{
	global $context;

	echo '
	<div class="title_bar">
		<h3 class="category_header">'.$context['TPortal']['tperror'].'</h3>
	</div>';

}

// Error article not published
function template_notpublished()
{
	global $context;
	echo '
<div style="padding-bottom: 4px;">
	<span class="clear upperframe"><span></span></span>
	<div class="roundframe"><div class="innerframe">
		<div style="line-height: 1.5em; text-align: center;">'.$context['TPortal']['tperror'].'</div>
	</div></div>
	<span class="lowerframe"><span></span></span>
</div>';

}

function template_tperror_below()
{
	return;
}

function template_tpnotify_above()
{
	global $context;

	echo '<div style="color: green; padding: 1em; background-color: #fdfffd; border: 2px solid; margin-bottom: 1em;">
			<div style="padding: 1em;">'.$context['TPortal']['tpnotify'].'</div>
		</div>';

}

function template_tpnotify_below()
{
	return;
}

// the TP tabs routine
function template_tptabs_above()
{
	global $context;

	if(!empty($context['TPortal']['tptabs']))
	{
		$buts = array();
		echo '
	<div class="tp_tabs">';
		foreach($context['TPortal']['tptabs'] as $tab)
			$buts[] = '<a' . ($tab['is_selected'] ? ' class="tp_active"' : '') . ' href="' . $tab['href'] . '">' . $tab['title'] . '</a>';

		echo implode(' | ', $buts) , '
	</div>';
	}
}

function template_tptabs_below()
{
	global $context;

}

// article layout types
function article_renders($type = 1, $single = false, $first = false)
{
	global $context;
	$code = '';
	// decide the header style, different for forumposts
    $usetitlestyle = in_array($context['TPortal']['article']['frame'], array('theme', 'title'));
    $useframestyle = in_array($context['TPortal']['article']['frame'], array('theme', 'frame'));
	$divheader = isset($context['TPortal']['article']['boardnews']) ? $context['TPortal']['boardnews_divheader'] : 'title_bar';
	$headerstyle = isset($context['TPortal']['article']['boardnews']) ? $context['TPortal']['boardnews_headerstyle'] : 'category_header';
	$divbody = isset($context['TPortal']['article']['boardnews']) ? $context['TPortal']['boardnews_divbody'] : ($usetitlestyle ? 'content' : 'content');
	$showtitle = in_array('title', $context['TPortal']['article']['visual_options']);

	if($type == 1)
	// Layout type: normal articles
	{
		$code = '
	<div class="tparticle render1" style="overflow: hidden;">
		<div></div>
		' . ($usetitlestyle ? '<div class="'. $divheader .'">' : '<div style="padding: 0 1em;">') . '
			' . ($usetitlestyle ? '<h3 class="' . $headerstyle . '">' .($showtitle ? '{article_title}' : '&nbsp;'). '</h3>' :  '<h3>' .($showtitle ? '{article_title}' : '' ). '</h3>') . '
		</div>
		<div' . (($useframestyle) ? ' class="' .$divbody. '" ' : '') . '>
			' . ($usetitlestyle ? '' : ($useframestyle ? '<span class="topslice"><span></span></span>' : '')) . '
			<div class="article_info">
				' . (!$single ? '{article_avatar}' : '') . '
				{article_options}
				{article_category}
				{article_date}
				{article_author}
				{article_views}
				{article_rating}
			' . ($single ? '{article_print}' : '') . '
			</div>
			<div class="tp_underline"></div>
			<div class="article_padding">
				<div style="clear: both;"></div>
				{article_text}
				<p class="clearthefloat"></p>
				' . (!isset($context['TPortal']['article']['boardnews']) ? '{article_bookmark}' : '') . '
				' . (!$single ? '{article_comments_total}' : '') . '
				' . (isset($context['TPortal']['article']['boardnews']) ? '{article_boardnews}' : '') . '
				' . ($single ? '
					{article_moreauthor}
					<div class="article_padding">
						{article_comments}
					</div>
					{article_morelinks}' : '') . '
			</div>
			' . ($useframestyle ? '<span class="botslice"><span></span></span>' : '') . '
		</div>
	</div>';
	}
	elseif($type == 2)
	// Layout type: 1st normal + avatars
	{
		if($first)
		$code = '
	<div class="tparticle render1" style="overflow: hidden;">
		<div></div>
		' . ($usetitlestyle ? '<div class="'. $divheader .'">' : '<div style="padding: 0 1em;">') . '
			' . ($usetitlestyle ? '<h3 class="' . $headerstyle . '">' .($showtitle ? '{article_title}' : '&nbsp;'). '</h3>' :  '<h3>' .($showtitle ? '{article_title}' : '' ). '</h3>') . '
		</div>
		<div' . (($useframestyle) ? ' class="' .$divbody. '" ' : '') . '>
			' . ($usetitlestyle ? '' : ($useframestyle ? '<span class="topslice"><span></span></span>' : '')) . '
			<div class="article_info">
				' . (!$single ? '{article_avatar}' : '') . '
				{article_options}
				{article_category}
				{article_date}
				{article_author}
				{article_views}
				{article_rating}
			' . ($single ? '{article_print}' : '') . '
			</div>
			<div class="tp_underline"></div>
			<div class="article_padding">
				<div style="clear: both;"></div>
				{article_text}
				<p class="clearthefloat"></p>
				' . (!isset($context['TPortal']['article']['boardnews']) ? '{article_bookmark}' : '') . '
				' . (!$single ? '{article_comments_total}' : '') . '
				' . (isset($context['TPortal']['article']['boardnews']) ? '{article_boardnews}' : '') . '
				' . ($single ? '
					{article_moreauthor}
					<div class="article_padding">
						{article_comments}
					</div>
					{article_morelinks}' : '') . '
			</div>
			' . ($useframestyle ? '<span class="botslice"><span></span></span>' : '') . '
		</div>
	</div>';
		else
			$code = '
	<div class="tparticle render2">
		<div' . ($useframestyle ? ' class="' .$divbody. '" ' : '') . '>
			<span class="topslice"><span></span></span>
			<div class="article_header">
				<div class="article_iconcolumn">{article_iconcolumn}</div>
				{article_options}
				' . ($showtitle ? '<h2 class="article_title" style="padding-left: 0;">{article_title}</h2>' : '') . '
			<div class="article_info" style="padding-left: 0;">
				{article_author}
				{article_category}
				{article_date}
				{article_views}
				{article_rating}
			' . ($single ? '{article_print}' : '') . '
			</div>
			</div>
			<div class="tp_underline"></div>
			<div class="article_padding">
				{article_text}
				<p class="clearthefloat"></p>
				' . (!isset($context['TPortal']['article']['boardnews']) && !$single ? '{article_bookmark}' : '') . '
				' . (!$single ? '{article_comments_total}' : '') . '
				' . (isset($context['TPortal']['article']['boardnews']) ? '{article_boardnews}' : '') . '
			</div>
			' . ($single ? '
			<div class="tp_container">
				<div class="tp_twocolumn">
					{article_bookmark}
				</div>
				<div class="tp_twocolumn">
					{article_moreauthor}
				</div>
			</div>
			<div class="article_padding>
				{article_comments}
				</div>
			{article_morelinks}' : '') . '
			' . ($useframestyle ? '<span class="botslice"><span></span></span>' : '') . '
		</div>
	</div>';
	}
	elseif($type == 4)
	// Layout type: articles + icons
	{
		$code = '
	<div class="tparticle render4">
		<div></div>
		' . ($usetitlestyle ? '<div class="' .$divheader.'">' : '<div style="padding: 0 1em;">') . '
			<h3 class="' .($usetitlestyle ? $headerstyle : ''). '">{article_title}&nbsp;</h3>
		</div>
		<div' . ($useframestyle ? ' class="' .$divbody. '" ' : '') . '>
			' . ($usetitlestyle ? '' : ($useframestyle ? '<span class="topslice"><span></span></span>' : '')) . '
			<div class="article_info">
				<div class="article_picturecolumn">{article_picturecolumn}</div>
				{article_options}
				{article_category}
				{article_date}
				{article_author}
				{article_views}
				{article_rating}
				' . ($single ? '{article_print}' : '') . '
			</div>
			<p class="clearthefloat"></p>
			<div class="tp_underline"></div>
			<div class="article_padding">
				{article_text}
				<p class="clearthefloat"></p>
				' . (!isset($context['TPortal']['article']['boardnews']) && !$single ? '{article_bookmark}' : '') . '
				' . (!$single ? '{article_comments_total}' : '') . '
				' . (isset($context['TPortal']['article']['boardnews']) ? '{article_boardnews}' : '') . '
			</div>
		' . ($single ? '
			<div class="tp_container">
				{article_bookmark}
				{article_moreauthor}
			</div>
			<div class="article_padding">
				{article_comments}
			</div>
			{article_morelinks}' : '') . '
			' . (($useframestyle) ? '<span class="botslice"><span></span></span>' : '') . '
		</div>
	</div>';
	}
	elseif($type == 8)
	// Layout type: articles + icons2
	{
		$code = '
	<div class="tparticle render8">
		<div ' . ($useframestyle ? 'class="' .$divbody. '"' : '') . ' style="margin: 0;">
		' . ($useframestyle ? '<span class="topslice"><span></span></span>' : '') . '
				<div class="article_header">
					<div class="article_picturecolumn">{article_picturecolumn}</div>
					{article_options}
					' .($showtitle ? '<h2 class="article_title" style="padding-left: 0;">{article_title}</h2>' : ''). '
				<div class="article_info" style="padding-left: 0;">
					{article_category}
					{article_date}
					{article_author}
					{article_views}
					{article_rating}
				' . ($single ? '{article_print}' : '') . '
				</div>
				</div>
				<div class="tp_underline"></div>
				<div class="article_padding">
					{article_text}
					<p class="clearthefloat"></p>
					' . (!isset($context['TPortal']['article']['boardnews']) && !$single ? '{article_bookmark}' : '') . '
					' . (!$single ? '{article_comments_total}' : '') . '
					' . (isset($context['TPortal']['article']['boardnews']) ? '{article_boardnews}' : '') . '
				</div>
		' . ($single ? '
				{article_bookmark}
				{article_moreauthor}
				<div class="article_padding">
					{article_comments}
				</div>
				{article_morelinks}' : '') . '
			' . ($useframestyle ? '<span class="botslice"><span></span></span>' : '') . '
		</div>
	</div>';
	}
	elseif($type == 6)
	// Layout type: simple articles
	{
		if($single)
			$code = '
	<div class="tparticle render6">
		<div class="' . ($useframestyle ? 'content' : '') . '">
		' . ($useframestyle ? '<span class="topslice"><span></span></span>' : '') . '
			<div class="article_header" style="padding-bottom: 0.5em;">
				{article_options}
				<h2 class="article_title">{article_title}</h2>
			</div>
			<div class="article_info">
				{article_date}
				{article_author}
				{article_views}
				{article_rating}
				{article_print}
			</div>
			<div class="tp_underline"></div>
			<div class="article_padding">
				{article_text}
				<p class="clearthefloat"></p>
				{article_bookmark}
				' . (!$single ? '{article_comments_total}' : '') . '
				{article_moreauthor}
				{article_comments}
			</div>
			{article_morelinks}
			' . ($useframestyle ? '<span class="botslice"><span></span></span>' : '') . '
		</div>
	</div>';
		else
			$code = '
	<div class="tparticle render6">
		<div class="' . ($useframestyle ? 'content' : '') .'">
		' . ($useframestyle ? '<span class="topslice"><span></span></span>' : '') . '
			<div class="article_header" style="padding-bottom: 0.5em;">
				{article_options}
				<h2 class="article_title">{article_title}</h2>
			</div>
			<div class="tp_underline"></div>
			<div class="article_padding">
				{article_text}
				<p class="clearthefloat"></p>
				{article_comments_total}
				' . (isset($context['TPortal']['article']['boardnews']) ? '{article_boardnews}' : '') . '
			</div>
		' . ($useframestyle ? '<span class="botslice"><span></span></span>' : '') . '
		</div>
	</div>';
	}
	elseif($type == 5)
	// Layout type: normal + links
	{
		if($first)
		$code = '
	<div class="tparticle render1" style="overflow: hidden;">
		<div></div>
		' . ($usetitlestyle ? '<div class="'. $divheader .'">' : '<div style="padding: 0 1em;">') . '
			' . ($usetitlestyle ? '<h3 class="' . $headerstyle . '">' .($showtitle ? '{article_title}' : '&nbsp;'). '</h3>' :  '<h3>' .($showtitle ? '{article_title}' : '' ). '</h3>') . '
		</div>
		<div' . (($useframestyle) ? ' class="' .$divbody. '" ' : '') . '>
			' . ($usetitlestyle ? '' : ($useframestyle ? '<span class="topslice"><span></span></span>' : '')) . '
			<div class="article_info">
				' . (!$single ? '{article_avatar}' : '') . '
				{article_options}
				{article_category}
				{article_date}
				{article_author}
				{article_views}
				{article_rating}
			' . ($single ? '{article_print}' : '') . '
			</div>
			<div class="tp_underline"></div>
			<div class="article_padding">
				<div style="clear: both;"></div>
				{article_text}
				<p class="clearthefloat"></p>
				' . (!isset($context['TPortal']['article']['boardnews']) ? '{article_bookmark}' : '') . '
				' . (!$single ? '{article_comments_total}' : '') . '
				' . (isset($context['TPortal']['article']['boardnews']) ? '{article_boardnews}' : '') . '
				' . ($single ? '
					{article_moreauthor}
					<div class="article_padding">
						{article_comments}
					</div>
					{article_morelinks}' : '') . '
			</div>
			' . ($useframestyle ? '<span class="botslice"><span></span></span>' : '') . '
		</div>
	</div>';
		else
			$code = '
	' . ($showtitle ? '<div class="' . $divheader . '">
		<h3 class="' . $headerstyle . '" style="font-weight: normal;">{article_title}</h3>
	</div>': '') . '';
	}
	elseif($type == 3)
	// Layout type: 1st avatar + links
	{
		if($first)
			$code = '
	<div class="tparticle render3">
		<div></div>
		' . ($usetitlestyle ? '<div class="'. $divheader .'">' : '<div style="padding: 0 1em;">') . '
		' . ($usetitlestyle ? '<h3 class="' . $headerstyle . '">' .($showtitle ? '{article_title}' : '&nbsp;'). '</h3>' :  '<h3>' .($showtitle ? '{article_title}' : '' ). '</h3>') . '
		</div>
		<div' . ($useframestyle ? ' class="' .$divbody. '" ' : '') . '>
			' . ($usetitlestyle ? '' : ($useframestyle ? '<span class="topslice"><span></span></span>' : '')) . '
			<div class="article_header">
				<div class="article_iconcolumn">{article_iconcolumn}</div>
				<div class="article_info" style="padding-left: 0;padding-top: 0;">
					{article_options}
					{article_author}
					{article_category}
					{article_date}
					{article_views}
					{article_rating}
				' . ($single ? '{article_print}' : '') . '
				</div>
			</div>
			<div class="tp_underline"></div>
			<div class="article_padding">
				{article_text}
				<p class="clearthefloat"></p>
				' . (!isset($context['TPortal']['article']['boardnews']) && !$single ? '{article_bookmark}' : '') . '
				' . (!$single ? '{article_comments_total}' : '') . '
				' . (isset($context['TPortal']['article']['boardnews']) ? '{article_boardnews}' : '') . '
			</div>
			' . ($single ? '
			<div>
				<div class="tp_container">
					{article_bookmark}
					{article_moreauthor}
				</div>
				<div class="article_padding">
					{article_comments}
				</div>
				{article_morelinks}
			</div>' : '') . '
			' . ($useframestyle ? '<span class="botslice"><span></span></span>' : '') . '
		</div>
	</div>';
		else
			$code = '
	' . ($showtitle ? '<div style="padding: 2px 1em;">
		<div class="align_right">
			<strong>{article_title}</strong>
		</div>
		{article_date}
		<hr />
	</div>': '') . '';
	}
	elseif($type == 9)
	// Layout type: just links
	{
		if($single)
		$code = '
	<div class="tparticle render1" style="overflow: hidden;">
		<div></div>
		' . ($usetitlestyle ? '<div class="'. $divheader .'">' : '<div style="padding: 0 1em;">') . '
			' . ($usetitlestyle ? '<h3 class="' . $headerstyle . '">' .($showtitle ? '{article_title}' : '&nbsp;'). '</h3>' :  '<h3>' .($showtitle ? '{article_title}' : '' ). '</h3>') . '
		</div>
		<div' . (($useframestyle) ? ' class="' .$divbody. '" ' : '') . '>
			' . ($usetitlestyle ? '' : ($useframestyle ? '<span class="topslice"><span></span></span>' : '')) . '
			<div class="article_info">
				' . (!$single ? '{article_avatar}' : '') . '
				{article_options}
				{article_category}
				{article_date}
				{article_author}
				{article_views}
				{article_rating}
			' . ($single ? '{article_print}' : '') . '
			</div>
			<div class="tp_underline"></div>
			<div class="article_padding">
				<div style="clear: both;"></div>
				{article_text}
				<p class="clearthefloat"></p>
				' . (!isset($context['TPortal']['article']['boardnews']) ? '{article_bookmark}' : '') . '
				' . (!$single ? '{article_comments_total}' : '') . '
				' . (isset($context['TPortal']['article']['boardnews']) ? '{article_boardnews}' : '') . '
				' . ($single ? '
					{article_moreauthor}
					<div class="article_padding">
						{article_comments}
					</div>
					{article_morelinks}' : '') . '
			</div>
			' . ($useframestyle ? '<span class="botslice"><span></span></span>' : '') . '
		</div>
	</div>';
		else
			$code = '
	<div class="render9">
		<div class="content" style="padding: 0;">
			<span class="topslice"><span></span></span>
			<div class="article_padding align_right">
				<strong>{article_title}</strong>
				{article_date}
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';
	}
	elseif($type == 7)
	// Layout type: use custom template
	{
		$code = $context['TPortal']['frontpage_template'];
	}
	return $code;
}

/* ********************************************** */
/* these are the prototype functions that can be called from an article template */
function article_edit() { return; }
function article_date($render = true)
{
	global $context;

	$data = '';
	if(in_array('date',$context['TPortal']['article']['visual_options'])) {
	    $data =  '<div class="article_date">' . (standardTime($context['TPortal']['article']['date'])) . '</div>';
    }

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_iconcolumn($render = true)
{
	global $context, $settings;

	if(!empty($context['TPortal']['article']['avatar'])) {
		$data = '
        <div style="overflow: hidden;">
            ' . $context['TPortal']['article']['avatar'] . '
        </div>';
    }
	else {
        $data = '
        <div style="overflow: hidden;">
            <img src="' . $settings['tp_images_url'] . '/TPnoimage' . (isset($context['TPortal']['article']['boardnews']) ? '_forum' : '') . '.png" alt="" />
        </div>';
    }

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_picturecolumn($render = true)
{
	global $context, $settings, $boardurl;

	if(!empty($context['TPortal']['article']['illustration']) && !isset($context['TPortal']['article']['boardnews'])) {
		$data = '
	<div class="article_picture" style="width: '.$context['TPortal']['icon_width'].'px; max-height: '.$context['TPortal']['icon_width'].'px;"><img src="' . $boardurl . '/tp-files/tp-articles/illustrations/' . $context['TPortal']['article']['illustration'] . '"></div>';
    }
	elseif(!empty($context['TPortal']['article']['illustration']) && isset($context['TPortal']['article']['boardnews']) && ($context['TPortal']['use_attachment']==1)) {
		$data = '
	    <div class="article_picture" style="width: '.$context['TPortal']['icon_width'].'px; max-height: '.$context['TPortal']['icon_width'].'px;"><img src="' . $context['TPortal']['article']['illustration'] . '"></div>';
	}
    else {
		$data = '
	<div class="article_picture" style="width: '.$context['TPortal']['icon_width'].'px; max-height: '.$context['TPortal']['icon_width'].'px;"><img src="' . $settings['tp_images_url'] . '/TPno_illustration.png"></div>';
    }

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_shortdate($render = true)
{
	global $context;

    $data = '';

	if(in_array('date',$context['TPortal']['article']['visual_options'])) {
		$data = '<div class="article_shortdate">' . tpstandardTime($context['TPortal']['article']['date'], true, '%d %b %Y').' - </div>';
    }

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_boardnews($render = true)
{
	global $context, $scripturl, $txt;

	if(!isset($context['TPortal']['article']['replies'])) {
		return;
    }

	$data = '
		<div class="article_boardnews">
			<a href="' . $scripturl . '?topic=' . $context['TPortal']['article']['id'] . '.0">' . $context['TPortal']['article']['replies'] . ' ' . ($context['TPortal']['article']['replies'] == 1 ? $txt['tp-comment'] : $txt['tp-comments']) . '</a>';
	if($context['TPortal']['article']['locked'] == 0 && !$context['user']['is_guest']) {
		$data .= '
			&nbsp;|&nbsp;' . '<a href="' . $scripturl . '?action=post;topic=' . $context['TPortal']['article']['id'] . '.' . $context['TPortal']['article']['replies'] . ';num_replies=' . $context['TPortal']['article']['replies'] . '">' . $txt['tp-writecomment']. '</a>';
    }

	$data .= '
		</div>';

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_author($render = true)
{
	global $scripturl, $txt, $context;

    $data = '';

	if(in_array('author', $context['TPortal']['article']['visual_options'])) {
		if($context['TPortal']['article']['date_registered'] > 1000) {
			$data = '<div class="article_author">
		'. $txt['tp-by'] . ' <a href="' . $scripturl . '?action=profile;u=' . $context['TPortal']['article']['author_id'] . '">' . $context['TPortal']['article']['real_name'] . '</a></div>';
        }
		else {
			$data = '<div class="article_author">
		' . $txt['tp-by'] . ' ' . $context['TPortal']['article']['real_name'] . '</div>';
        }
	}

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_views($render = true)
{
	global $txt, $context;

    $data = '';

	if(in_array('views',$context['TPortal']['article']['visual_options'])) {
		$data = '
		<div class="article_views"> ' . $txt['tp-views'] . ': ' . $context['TPortal']['article']['views'] . '</div>';
    }

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_comments_total($render = true)
{
	global $scripturl, $txt, $context;

	$data = '';

	if((in_array('comments', $context['TPortal']['article']['visual_options'])) || (in_array('commentallow', $context['TPortal']['article']['visual_options']))) {
		$data = '
		<div class="article_boardnews">
		<a href="' . $scripturl . '?page=' . (!empty($context['TPortal']['article']['shortname']) ? $context['TPortal']['article']['shortname'] : $context['TPortal']['article']['id']) . '#tp-comment">' .$context['TPortal']['article']['comments']. ' ' . ($context['TPortal']['article']['comments'] == 1 ? $txt['tp-comment'] : $txt['tp-comments']) . '</a>';

	if(in_array('commentallow', $context['TPortal']['article']['visual_options']) && isset($context['TPortal']['can_artcomment']) == 1) {
		$data .= '
			&nbsp;|&nbsp;' . '<a href="' . $scripturl . '?page=' . (!empty($context['TPortal']['article']['shortname']) ? $context['TPortal']['article']['shortname'] : $context['TPortal']['article']['id']) . '#tp-comment">' . $txt['tp-writecomment']. '</a>';
	}

		$data .= '
			</div>';
	}

	if($render) {
		echo $data;
	}
	else {
		return $data;
	}
}

function article_title($render = true)
{
	global $scripturl, $context;

    $data = '';

	if(in_array('title',$context['TPortal']['article']['visual_options'])) {
		if(isset($context['TPortal']['article']['boardnews'])) {
			$data = '
		<a href="' . $scripturl . '?topic=' . $context['TPortal']['article']['id'] . '">' . $context['TPortal']['article']['subject'] . '</a>';
		}
        else {
			$data = '
		<a href="' . $scripturl . '?page=' . (!empty($context['TPortal']['article']['shortname']) ? $context['TPortal']['article']['shortname'] : $context['TPortal']['article']['id']) . '">' . $context['TPortal']['article']['subject'] . '</a>';
        }
	}

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_category($render = true)
{
	global $scripturl, $txt, $context;

    $data = '';

	$catNameOrId = !empty($context['TPortal']['article']['category_shortname']) ? $context['TPortal']['article']['category_shortname'] : $context['TPortal']['article']['category'];

	if(!empty($context['TPortal']['article']['category_name'])) {
		if(isset($context['TPortal']['article']['boardnews'])) {
			$data = '
		<div class="article_category">' . $txt['tp-fromcategory'] . '<a href="' . $scripturl . '?board=' . $catNameOrId . '">' . $context['TPortal']['article']['category_name'] . '</a></div>';
        }
		else {
			if(in_array('catlist', $context['TPortal']['article']['visual_options'])) {
				$data = '
			<div class="article_category">' . $txt['tp-fromcategory'] . '<a href="' . $scripturl . '?cat=' . $catNameOrId . '">' . $context['TPortal']['article']['category_name'] . '</a></div>';
			}
        }
	}

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_lead($render = true)
{
	global $context;

    $data = '';

	if(in_array('lead',$context['TPortal']['article']['visual_options'])) {
		$data = '<div class="article_lead">' . TPSubs::getInstance()->renderArticle('intro') . '</div>';
    }

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_options($render = true)
{
	global $scripturl, $txt, $context, $settings;

    $data = '';

	if(!isset($context['TPortal']['article']['boardnews'])) {
		// give 'em a edit link? :)
		if(allowedTo('tp_articles') && ($context['TPortal']['hide_editarticle_link']==1)) {
			$data .= '
					<a href="' . $scripturl . '?action=admin;area=tparticles;sa=editarticle;article=' . $context['TPortal']['article']['id'] . '"><img style="margin: 2px 4px 0 0;float:right" src="' .$settings['tp_images_url']. '/TPedit2.png" alt="" title="'.$txt['tp-edit'].'" /></a>';
        }
		// their own article?
		elseif(allowedTo('tp_editownarticle') && !allowedTo('tp_articles') && ($context['TPortal']['article']['author_id'] == $context['user']['id']) && $context['TPortal']['hide_editarticle_link']==1 && $context['TPortal']['article']['locked']!=1) {
			$data .= '
					<a href="' . $scripturl . '?action=admin;area=tparticles;sa=editarticle;article=' . $context['TPortal']['article']['id'] . '"><img style="margin: 2px 4px 0 0;float:right" src="' .$settings['tp_images_url']. '/TPedit2.png" alt="" title="'.$txt['tp-edit'].'" /></a>';
        }
	}

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_print($render = true)
{
	global $scripturl, $txt, $context;

    $data = '';

	if($context['TPortal']['print_articles']==1) {
		if(isset($context['TPortal']['article']['boardnews']) && !$context['user']['is_guest']) {
			$data .= '
					<div class="article_rating"><a href="' . $scripturl . '?action=printpage;topic=' . $context['TPortal']['article']['id'] . '">' . $txt['print_page'] . '</a></div>';
        }
		elseif (!$context['user']['is_guest']) {
			$data .= '
					<div class="article_rating"><a href="' . $scripturl . '?page=' . $context['TPortal']['article']['id'] . ';print">' . $txt['tp-print'] . '</a></div>';
        }
	}

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_text($render = true)
{
	$data = '<div class="article_bodytext">' . TPSubs::getInstance()->renderArticle() . '</div>';

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_rating($render = true)
{
	global $context;

    $data = '';

	if(in_array('rating',$context['TPortal']['article']['visual_options'])) {
		if(!empty($context['TPortal']['article']['voters'])) {
			$data = '<div class="article_rating">' . (render_rating($context['TPortal']['article']['rating'], count(explode(',', $context['TPortal']['article']['voters'])), $context['TPortal']['article']['id'], (isset($context['TPortal']['article']['can_rate']) ? $context['TPortal']['article']['can_rate'] : false), $render)) . '</div>';
		}
        else {
			 $data = '<div class="article_rating">' . (render_rating($context['TPortal']['article']['rating'], 0, $context['TPortal']['article']['id'], (isset($context['TPortal']['article']['can_rate']) ? $context['TPortal']['article']['can_rate'] : false), $render)) . '</div>';
        }
	}

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_moreauthor($render = true)
{
	global $scripturl, $txt, $context;

    $data = '';

	if(in_array('avatar', $context['TPortal']['article']['visual_options'])) {
		$data .= '<div>';
		if( $context['TPortal']['article']['date_registered'] > 1000 ) {
			$data .= '
                <div class="article_authorinfo tp_pad">
                    <h2 class="author_h2">'.$txt['tp-authorinfo'].'</h2>
                    ' . ( !empty($context['TPortal']['article']['avatar']) ? '<a class="tp_avatar_author" href="' . $scripturl . '?action=profile;u=' . $context['TPortal']['article']['author_id'] . '" title="' . $context['TPortal']['article']['real_name'] . '">' . $context['TPortal']['article']['avatar'] . '</a>' : '') . '
                    <div class="authortext">
                        <a href="' . $scripturl . '?action=profile;u=' . $context['TPortal']['article']['author_id'] . '">' . $context['TPortal']['article']['real_name'] . '</a>' . $txt['tp-poster1'] . $context['forum_name'] . $txt['tp-poster2'] . standardTime($context['TPortal']['article']['date_registered']) . $txt['tp-poster3'] .
                        $context['TPortal']['article']['posts'] . $txt['tp-poster4'] . standardTime($context['TPortal']['article']['last_login']) . '.
                    </div>
                </div>';
        }
		else {
			$data .= '
                <div class="article_authorinfo tp_pad">
                    <h3>'.$txt['tp-authorinfo'].'</h3>
                    <div class="authortext">
                        <em>' . $context['TPortal']['article']['real_name'] . $txt['tp-poster5'] .  '</em>
                    </div>
                </div>';
        }
		$data .= '</div>';
	}

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_avatar($render = true)
{
	global $scripturl, $context;

    $data = '';

	if(in_array('avatar', $context['TPortal']['article']['visual_options'])) {
		$data = (!empty($context['TPortal']['article']['avatar']) ? '<div class="tp_avatar_single" ><a href="' . $scripturl . '?action=profile;u=' . $context['TPortal']['article']['author_id'] . '" title="' . $context['TPortal']['article']['real_name'] . '">' . $context['TPortal']['article']['avatar'] . '</a></div>' : '');
    }

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_bookmark($render = true)
{
	global $scripturl, $settings, $context;

    $data = '';

	if(in_array('social',$context['TPortal']['article']['visual_options'])) {
		$data .= '
	<div>
		<div class="article_socialbookmark">';
		if (!$context['TPortal']['hide_article_facebook']=='1') {
		    $data .= '<a href="http://www.facebook.com/sharer.php?u=' . $scripturl . '?page=' . (!empty($context['TPortal']['article']['shortname']) ? $context['TPortal']['article']['shortname'] : $context['TPortal']['article']['id']) . '" target="_blank"><img class="tp_social" src="' . $settings['tp_images_url'] . '/social/facebook.png" alt="Share on Facebook!" title="Share on Facebook!" /></a>';
		}
		if (!$context['TPortal']['hide_article_twitter']=='1') {
			$data .= '<a href="http://twitter.com/home/?status=' . $scripturl.'?page='. (!empty($context['TPortal']['article']['shortname']) ? $context['TPortal']['article']['shortname'] : $context['TPortal']['article']['id']) . '" target="_blank"><img class="tp_social" title="Share on Twitter!" src="' . $settings['tp_images_url'] . '/social/twitter.png" alt="Share on Twitter!" /></a>';
		}
		if (!$context['TPortal']['hide_article_reddit']=='1') {
			$data .= '<a href="http://www.reddit.com/submit?url=' . $scripturl . '?page=' . (!empty($context['TPortal']['article']['shortname']) ? $context['TPortal']['article']['shortname'] : $context['TPortal']['article']['id']) . '" target="_blank"><img class="tp_social" src="' . $settings['tp_images_url'] . '/social/reddit.png" alt="Reddit" title="Reddit" /></a>';
		}
		if (!$context['TPortal']['hide_article_digg']=='1') {
			$data .= '<a href="http://digg.com/submit?url=' . $scripturl.'?page='. (!empty($context['TPortal']['article']['shortname']) ? $context['TPortal']['article']['shortname'] : $context['TPortal']['article']['id']) . '&title=' . $context['TPortal']['article']['subject'].'" target="_blank"><img class="tp_social" title="Digg this story!" src="' . $settings['tp_images_url'] . '/social/digg.png" alt="Digg this story!" /></a>';
		}
		if (!$context['TPortal']['hide_article_delicious']=='1') {
			$data .= '<a href="http://del.icio.us/post?url=' . $scripturl.'?page=' . (!empty($context['TPortal']['article']['shortname']) ? $context['TPortal']['article']['shortname'] : $context['TPortal']['article']['id']) . '&title=' . $context['TPortal']['article']['subject'] . '" target="_blank"><img class="tp_social" src="' . $settings['tp_images_url'] . '/social/delicious.png" alt="Del.icio.us" title="Del.icio.us" /></a>';
		}
		if (!$context['TPortal']['hide_article_stumbleupon']=='1') {
			$data .= '<a href="http://www.stumbleupon.com/submit?url=' . $scripturl . '?page=' . (!empty($context['TPortal']['article']['shortname']) ? $context['TPortal']['article']['shortname'] : $context['TPortal']['article']['id']) . '" target="_blank"><img class="tp_social" src="' . $settings['tp_images_url'] . '/social/stumbleupon.png" alt="StumbleUpon" title="Stumbleupon" /></a>';
		}
			$data .='
		</div>
	</div>';
	}

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function article_comments($render = true)
{
	global $scripturl, $txt, $settings, $context;

		$data = '';

	if((in_array('comments', $context['TPortal']['article']['visual_options'])) || (in_array('commentallow', $context['TPortal']['article']['visual_options']))) {
		$data .= '
	<a name="tp-comment">
	<div></div>
	<h2 class="category_header article_extra">' . $txt['tp-comments'] . ': ' . $context['TPortal']['article_comments_count'] . '' . (TPSubs::getInstance()->hidePanel('articlecomments', false, true, '5px 5px 0 5px')) . '</h2> ';
	}

	if(in_array('comments', $context['TPortal']['article']['visual_options']) && !$context['TPortal']['article_comments_count'] == 0) {
		$data .= '
	<div id="articlecomments" class="tp_commentsbox"' . (in_array('articlecomments',$context['tp_panels']) ? ' style="display: none;"' : '') . '>';

		$counter = 1;
		if(isset($context['TPortal']['article']['comment_posts'])) {
			foreach($context['TPortal']['article']['comment_posts'] as $comment) {
				$data .= '
					<div class="tp_article_comment ' . ($context['TPortal']['article']['author_id']!=$comment['poster_id'] ? 'mycomment' : 'othercomment') . '">
					<a id="comment'.$comment['id'].'"></a>';
				// can we edit the comment or are the owner of it?
				if(allowedTo('tp_articles') || $comment['poster_id'] == $context['user']['id'] && !$context['user']['is_guest']) {
					$data .= '<div class="floatright"><i><a class="active" href="' . $scripturl . '?action=tparticle;sa=killcomment;comment=' . $comment['id'] . '" onclick="javascript:return confirm(\'' . $txt['tp-confirmcommentdelete'] . '\')"><span>' . $txt['tp-delete'] . '</span></a></i></div>';
                }
				// not a guest
				if ($comment['poster_id'] > 0) {
					$data .= '
					<span class="tp_comment_author">' . (!empty($comment['avatar']['image']) ? $comment['avatar']['image'] : '') . '</span>';
                }
				$data .= '
					<strong>' . $counter++ .') ' . $comment['subject'] . '</strong>
					' . (($comment['is_new'] && $context['user']['is_logged']) ? '<a href="" id="newicon" class="new_posts" >' . $txt['new'] . '</a>' : '') . '';
				if ($comment['poster_id'] > 0) {
					$data .= '
						<div class="middletext" style="padding-top: 0.5em;"> '.$txt['tp-bycom'].' <a href="'.$scripturl.'?action=profile;u='.$comment['poster_id'].'">'.$comment['poster'].'</a>&nbsp;' . $txt['on'] . ' ' . $comment['date'] . '</div>';
				}
                else {
					$data .= '
						<div class="middletext" style="padding-top: 0.5em;"> '.$txt['tp-bycom'].' '.$txt['guest_title'].'&nbsp;'. $txt['on'] . ' ' . $comment['date'] . '</div>';
                }
				$data .= '
					<p class="clearthefloat"></p>
					<div class="textcomment"><div class="body">' . $comment['text'] . '</div></div>';
				$data .= '
				</div>';
			}
		}
		$data .= '
			</div>';
	}

	if(in_array('commentallow', $context['TPortal']['article']['visual_options']) && isset($context['TPortal']['can_artcomment'])==1) {
		$data .= '
			<div class="tp_pad">
				<form accept-charset="' . 'UTF-8' . '"  name="tp_article_comment" action="' . $scripturl . '?action=tparticle;sa=comment" method="post" style="margin: 0; padding: 0;">
						<input type="text" name="tp_article_comment_title" style="width: 99%;" value="Re: ' . strip_tags($context['TPortal']['article']['subject']) . '">
						<textarea style="width: 99%; height: 8em;" name="tp_article_bodytext"></textarea><br>';

	if (!empty($context['TPortal']['allow_links_article_comments'])==0) {
		$data .= '<em>'. $txt['tp-nolinkcomments'] . '<em>';
		}

		$data .= '
						<div class="tp_pad"><input type="submit" id="tp_article_comment_submit" class="button button_submit" value="' . $txt['tp-submit'] . '"></div>
						<input type="hidden" name="tp_article_type" value="article_comment">
						<input type="hidden" name="tp_article_id" value="' . $context['TPortal']['article']['id'] . '">
						<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
				</form>
			</div>';
	}
	elseif (in_array('commentallow', $context['TPortal']['article']['visual_options']) && isset($context['TPortal']['can_artcomment'])!=1) {
		$data .= '
			<div class="tp_pad"><em>' . $txt['tp-cannotcomment'] . '</em></div>';
    }

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }

}

function article_morelinks($render = true)
{
	global $scripturl, $txt, $context;

    $data = '';

	if(in_array('category',$context['TPortal']['article']['visual_options'])) {
		if(in_array('category',$context['TPortal']['article']['visual_options']) && isset($context['TPortal']['article']['others'])) {
			$data .= '
	<h2 class="category_header article_extra"><a href="' . $scripturl . '?cat='. (!empty($context['TPortal']['article']['category_shortname']) ? $context['TPortal']['article']['category_shortname'] : $context['TPortal']['article']['category']) .'">' . $txt['tp-articles'] . ' ' . $txt['in'] . ' &#171; ' . $context['TPortal']['article']['category_name'] . ' &#187;</span></a></h2>

	<div style="overflow: hidden;">
		<ul class="disc">';
			foreach($context['TPortal']['article']['others'] as $art) {
				$data .= '<li' . (isset($art['selected']) ? ' class="selected"' : '') . '><a href="' . $scripturl . '?page=' . (!empty($art['shortname']) ? $art['shortname'] : $art['id']) . '">' . html_entity_decode($art['subject']) . '</a></li>';
            }
			$data .= '
		</ul>
	</div>';
		}
	}

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function render_rating($total, $votes, $id, $can_rate = false, $render = true)
{
	global $txt, $context, $settings, $scripturl;

    $data = '';

	if(!is_numeric($total)) {
		$total = (int)$total;
    }

	if(!is_numeric($votes)) {
		$votes = (int)$votes;
    }

	if($total == 0 && $votes > 0) {
		$data .= ' '.  $txt['tp-ratingaverage'] . ' 0 (' . $txt['tp-ratingvotes'] . ' ' . $votes . ')';
    }
	elseif($total == 0 && $votes == 0) {
		$data .= ' '.  $txt['tp-ratingaverage'] . ' 0 (' . $txt['tp-ratingvotes'] . ' 0)';
    }
	else {
		$data .= ' '.  $txt['tp-ratingaverage'] . ' ' . ($context['TPortal']['showstars'] ? (str_repeat('<img src=" '. $settings['tp_images_url'].'/TPblue.png" style="width: .7em; height: .7em; margin-right: 2px;" alt="" />' , ceil($total/$votes))) : ceil($total/$votes)) . ' (' . $txt['tp-ratingvotes'] . ' ' . $votes . ')';
    }

	// can we rate it?
	if($context['TPortal']['single_article']) {
		if($context['user']['is_logged'] && $can_rate) {
				$data .= '
			<form action="' . $scripturl . '?action=tparticle;sa=rate_article" style="margin: 0; padding: 0; display: inline;" method="post">
				<select name="tp_article_rating" size="1" style="width: 4em;">';

				for($u=$context['TPortal']['maxstars'] ; $u>0 ; $u--) {
					$data .= '
					<option value="' . $u . '">' . $u . '</option>';
                }

				$data .= '
				</select>
				<input type="submit" name="tp_article_rating_submit" value="' . $txt['tp_rate'] . '">
				<input type="hidden" name="tp_article_type" value="article_rating">
				<input type="hidden" name="tp_article_id" value="' . $id . '">
				<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
			</form>';
		}
		else {
			if (!$context['user']['is_guest']) {
			    $data .= ' 	<em class="tp_article_rate smalltext">'. $txt['tp-dlhaverated'].'</em>';
            }
		}
	}

    if($render) {
        echo $data;
    }
    else {
        return $data;
    }
}

function tp_grids()
{
	// the built-in grids
	$grid = array(
		// vertical
		1 => array(
				'cols' => 1,
				'code' => '
			<div class="tp_container">
				<div class="tp_onecolumn">{featured}</div>
			</div>
			<div class="tp_container">
				<div class="tp_onecolumn">{col1}{col2}</div>
			</div>'
		),
		// featured 1 col, 2 cols
		2 => array(
				'cols' => 2,
				'code' => '
			<div class="tp_container">
				<div class="tp_onecolumn">{featured}</div>
			</div>
			<div class="tp_container">
				<div class="tp_twocolumn"><div class="tp_leftcol">{col1}</div></div>
				<div class="tp_twocolumn"><div class="tp_rightcol">{col2}</div></div>
			</div>'
		),
		// featured left col, rest right col
		3 => array(
				'cols' => 1,
				'code' => '
			<div class="tp_container">
				<div class="tp_twocolumn"><div class="tp_leftcol">{featured}</div></div>
				<div class="tp_twocolumn"><div class="tp_rightcol">{col1}{col2}</div></div>
			</div>'
		),
		// 2 cols
		4 => array(
				'cols' => 2,
				'code' => '
			<div class="tp_container">
				<div class="tp_twocolumn"><div class="tp_leftcol">{featured}{col1}</div></div>
				<div class="tp_twocolumn"><div class="tp_rightcol">{col2}</div></div>
			</div>'
		),
		// 2 cols, then featured at bottom
		5 => array(
				'cols' => 1,
				'code' => '
			<div class="tp_container">
				<div class="tp_onecolumn">{col1}{col2}</div>
			</div>
			<div class="tp_container">
				<div class="tp_onecolumn">{featured}</div>
			</div>'
		),
		// rest left col, featured right col
		6 => array(
				'cols' => 1,
				'code' => '
			<div class="tp_container">
				<div class="tp_twocolumn"><div class="tp_rightcol">{col1}{col2}</div></div>
				<div class="tp_twocolumn"><div class="tp_leftcol">{featured}</div></div>
			</div>'
		),
	);
	return $grid;
}

/* for blockarticles */
// This is the template for single article
function template_blockarticle()
{
	global $context;

	// use a customised template or the built-in?
	if(!empty($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['template'])) {
		TPSubs::getInstance()->render_template($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['template']);
    }
	else {
		TPSubs::getInstance()->render_template(blockarticle_renders());
    }
}

function blockarticle_renders()
{

	$code = '
	<div class="blockarticle render1">
		<div class="article_info">
			{blockarticle_author}
			{blockarticle_date}
			{blockarticle_views}
		</div>
		<div class="article_padding">{blockarticle_text}</div>
		<div class="article_padding">{blockarticle_moreauthor}</div>
	</div>
		';
	return $code;
}

function blockarticle_date($render = true)
{
	global $context;

	if(in_array('date',$context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['visual_options']))
		echo '
		<span class="article_date"> ' . (standardTime($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['date'])) . '</span>';
	else
		echo '';

}

function blockarticle_author($render = true)
{
	global $scripturl, $txt, $context;

	if(in_array('author',$context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['visual_options']))
	{
		if($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['date_registered'] > 1000)
			echo '
		<span class="article_author">' . $txt['tp-by'] . ' <a href="' . $scripturl . '?action=profile;u=' . $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['author_id'] . '">' . $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['real_name'] . '</a></span>';
		else
			echo '
		<span class="article_author">' . $txt['tp-by'] . ' ' . $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['real_name'] . '</span>';
	}
	else
		echo '';

}

function blockarticle_views($render = true)
{
	global $txt, $context;

	if(in_array('views',$context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['visual_options']))
		echo '
		<span class="article_views">' . $txt['tp-views'] . ': ' . $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['views'] . '</span>';
	else
		echo '';

}

function blockarticle_text($render = true)
{
	echo '
	<div class="article_bodytext">' . TPSubs::getInstance()->renderBlockArticle() . '</div>';

}

function blockarticle_moreauthor($render = true)
{
	global $scripturl, $txt, $context;

	if(in_array('avatar', $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['visual_options']))
	{
		if($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['date_registered'] > 1000)
			echo '
		<div class="article_authorinfo">
			<h3>'.$txt['tp-authorinfo'].'</h3>
			' . ( !empty($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['avatar']) ? '<a class="tp_avatar_author" href="' . $scripturl . '?action=profile;u=' . $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['author_id'] . '" title="' . $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['real_name'] . '">' . $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['avatar'] . '</a>' : '') . '
			<div class="authortext">
				<a href="' . $scripturl . '?action=profile;u=' . $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['author_id'] . '">' . $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['real_name'] . '</a>' . $txt['tp-poster1'] . $context['forum_name'] . $txt['tp-poster2'] . standardTime($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['date_registered']) . $txt['tp-poster3'] .
				$context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['posts'] . $txt['tp-poster4'] . standardTime($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['last_login']) . '.
			</div>
		</div>';
		else
			echo '
		<div class="article_authorinfo">
			<h3>'.$txt['tp-authorinfo'].'</h3>
			<div class="authortext">
				<em>' . $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['real_name'] . $txt['tp-poster5'] .  '</em>
			</div>
		</div>';
	}
	else
		echo '';

}

function category_childs()
{
	global $context, $scripturl;

	echo '
	<ul class="category_children">';
	foreach($context['TPortal']['category']['children'] as $ch => $child)
		if (!empty($context['TPortal']['category']['options']['showchild']) == 1)
			echo '<li><a href="' , $scripturl , '?cat=' , $child['id'] , '">' , $child['display_name'] ,' (' , $child['articlecount'] , ')</a></li>';

	echo '
	</ul>';

	return;
}

function template_subtab_above()
{
	global $context, $txt;

	if(isset($context['TPortal']['subtabs']) && ( is_countable($context['TPortal']['subtabs']) && count($context['TPortal']['subtabs']) > 1 ))
	{
		echo '
		<div class="tborder" style="margin-bottom: 0.5em;">
			<div class="cat_bar">
				<h3 class="category_header">' . $txt['tp-menus'] .'</h3>
			</div>
			<div style="padding: 2px; overflow: hidden;">';

		tp_template_button_strip($context['TPortal']['subtabs']);

		echo '
			</div>
		</div>';
	}
}

function template_subtab_below()
{
	return;
}

function template_tpadm_above()
{
	global $context, $txt;

	echo '
	<div  class="tpadmin_menu">
		<div class="cat_bar">
			<h3 class="category_header">' . $txt['tp-adminmenu'] .'</h3>
		</div>
		<span class="upperframe"><span></span></span>
		<div class="roundframe">';


	if(is_array($context['admin_tabs']) && count($context['admin_tabs']) > 0) {
		echo '
			<ul style="padding-bottom: 10px;">';
		foreach($context['admin_tabs'] as $ad => $tab) {
			echo '
				<li><div class="largetext">' , isset($context['admin_header'][$ad]) ? $context['admin_header'][$ad] : '' , '</div>
					';
			$tbas = array();
			foreach($tab as $tb) {
				$tbas[]='<a href="' . $tb['href'] . '">' .($tb['is_selected'] ? '<b>'.$tb['title'].'</b>' : $tb['title']) . '</a>';
            }

			// if new style...
			if($context['TPortal']['oldsidebar'] == 0) {
				echo '<div class="normaltext">' , implode(', ', $tbas) , '</div>
				</li>';
			}
            else {
				echo '<div class="middletext" style="margin: 0; line-height: 1.3em;">' , implode('<br>', $tbas) , '</div>
				</li>';
            }

		}
		echo '
			<div style="clear:both;"></div></ul>';
	}

	echo '
		</div>
		<span class="lowerframe"><span></span></span>
	</div>
	<div class="tpadmin_content" style="margin-top: 0;">';
}

function template_tpadm_below()
{
	echo '

	</div>';

	return;
}

// Format a time to make it look purdy.
function tpstandardTime($log_time, $show_today = true, $format)
{
	global $context, $user_info, $txt, $modSettings;

	$time = $log_time + ($user_info['time_offset'] + $modSettings['time_offset']) * 3600;

	// We can't have a negative date (on Windows, at least.)
	if ($log_time < 0)
		$log_time = 0;

	// Today and Yesterday?
	if ($modSettings['todayMod'] >= 1 && $show_today === true)
	{
		// Get the current time.
		$nowtime = forum_time();

		$then = @getdate($time);
		$now = @getdate($nowtime);

		// Try to make something of a time format string...
		$s = strpos($format, '%S') === false ? '' : ':%S';
		if (strpos($format, '%H') === false && strpos($format, '%T') === false)
		{
			$h = strpos($format, '%l') === false ? '%I' : '%l';
			$today_fmt = $h . ':%M' . $s . ' %p';
		}
		else
			$today_fmt = '%H:%M' . $s;

		// Same day of the year, same year.... Today!
		if ($then['yday'] == $now['yday'] && $then['year'] == $now['year'])
			return $txt['today'] . tpstandardTime($log_time, $today_fmt, $format);

		// Day-of-year is one less and same year, or it's the first of the year and that's the last of the year...
		if ($modSettings['todayMod'] == '2' && (($then['yday'] == $now['yday'] - 1 && $then['year'] == $now['year']) || ($now['yday'] == 0 && $then['year'] == $now['year'] - 1) && $then['mon'] == 12 && $then['mday'] == 31))
			return $txt['yesterday'] . tpstandardTime($log_time, $today_fmt, $format);
	}

	$str = !is_bool($show_today) ? $show_today : $format;

	if (setlocale(LC_TIME, $txt['lang_locale']))
	{
		foreach (array('%a', '%A', '%b', '%B') as $token)
			if (strpos($str, $token) !== false)
				$str = str_replace($token, !empty($txt['lang_capitalize_dates']) ? Util::ucwords(strftime($token, $time)) : strftime($token, $time), $str);
	}
	else
	{
		// Do-it-yourself time localization.  Fun.
		foreach (array('%a' => 'days_short', '%A' => 'days', '%b' => 'months_short', '%B' => 'months') as $token => $text_label)
			if (strpos($str, $token) !== false)
				$str = str_replace($token, $txt[$text_label][(int) strftime($token === '%a' || $token === '%A' ? '%w' : '%m', $time)], $str);
		if (strpos($str, '%p'))
			$str = str_replace('%p', (strftime('%H', $time) < 12 ? 'am' : 'pm'), $str);
	}

	// Windows doesn't support %e; on some versions, strftime fails altogether if used, so let's prevent that.
	if ($context['server']['is_windows'] && strpos($str, '%e') !== false)
		$str = str_replace('%e', ltrim(strftime('%d', $time), '0'), $str);

	// Format any other characters..
	return strftime($str, $time);
}

// Generate a strip of buttons.
function tp_template_button_strip($button_strip, $direction = 'top', $strip_options = array())
{
	global $context, $txt;

	if (!is_array($strip_options))
		$strip_options = array();

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value) {
		if (!isset($value['test']) || !empty($context[$value['test']])) {
				$buttons[] = '<a' . (isset($value['id']) ? ' id="button_strip_' . $value['id'] . '"' : '') . ' class="listlevel1 linklevel1 button_strip_' . $key . '' . ($value['active'] ? ' active' : '') . '" href="' . $value['url'] . '"' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . ' ><span>' . $txt[$value['text']] . '</span></a>';
		}
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	// Make the last one, as easy as possible.
	$buttons[count($buttons) - 1] = str_replace('<span>', '<span class="last">', $buttons[count($buttons) - 1]);

	echo '
		<div class="buttonlist', !empty($direction) ? ' align_' . $direction : '', '"', (empty($buttons) ? ' style="display: none;"' : ''), (!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"': ''), '>
			<ul style="margin:0px;padding:5px 0px;">',
				implode('', $buttons), '
			<p class="clearthefloat"></p></ul>
		</div>';
}

?>
