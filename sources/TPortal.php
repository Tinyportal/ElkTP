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
use \TinyPortal\Admin as TPAdmin;
use \TinyPortal\Article as TPArticle;
use \TinyPortal\Block as TPBlock;
use \TinyPortal\Category as TPCategory;
use \TinyPortal\Database as TPDatabase;
use \TinyPortal\Integrate as TPIntegrate;
use \TinyPortal\Mentions as TPMentions;
use \TinyPortal\Permissions as TPPermissions;
use \TinyPortal\Util as TPUtil;

define('TPVERSION', 100);

if (!defined('ELK')) {
	die('Hacking attempt...');
}

// TinyPortal startpage
function TPortalMain() {{{
	global $context;

	loadTemplate('TPortal');

}}}

// TinyPortal init
function TPortalInit() {{{
	global $context, $txt, $user_info, $settings, $modSettings;

    call_integration_hook('integrate_tp_pre_init');

	// has init been run before? if so return!
	if(isset($context['TPortal']['redirectforum'])) {
		return;
    }

	if(\loadLanguage('TPortal') == false) {
		\loadLanguage('TPortal', 'english');
    }

    $tpMention = TPMentions::getInstance();
    $tpMention->addJS();

	$context['TPortal'] = array();

	if(!isset($context['forum_name'])) {
		$context['forum_name'] = '';
    }

	$context['TPortal'] = array();
	// Add all the TP settings into ['TPortal']
	setupTPsettings();
    // Setup querystring
	$context['TPortal']['querystring'] = $_SERVER['QUERY_STRING'];
    
	// Include a ton of functions.
	require_once(SOURCEDIR . '/TPSubs.php');
	
	// go back on showing attachments..
	if(isset($_GET['action']) && $_GET['action'] == 'dlattach') {
		return;
    }

	// Grab the SSI for its functionality
	require_once(BOARDDIR. '/SSI.php');

	// set up the layers, but not for certain actions
	if(!isset($_REQUEST['preview']) && !isset($_REQUEST['quote']) && !isset($_REQUEST['xml']) && !isset($aoptions['nolayer'])) {
        Template_Layers::getInstance()->add($context['TPortal']['hooks']['tp_layer']);
    }

	\loadTemplate('TPsubs');
	\loadTemplate('TPBlockLayout');

	// is the permanent theme option set?
	if(isset($_GET['permanent']) && !empty($_GET['theme']) && $context['user']['is_logged']) {
		TP_permaTheme($_GET['theme']);
    }

	// Load the stylesheet stuff
	tpLoadCSS();

	// if we are in permissions admin section, load all permissions
	if((isset($_GET['action']) && $_GET['action'] == 'permissions') || (isset($_GET['area']) && $_GET['area'] == 'permissions')) {
		TPPermissions::getInstance()->collectPermissions();
    }

	// finally..any errors finding an article or category?
	if(!empty($context['art_error'])) {
		fatal_error($txt['tp-articlenotexist'], false);
    }

	if(!empty($context['cat_error'])) {
		fatal_error($txt['tp-categorynotexist'], false);
    }

    call_integration_hook('integrate_tp_post_init');

    // set cookie change for selected upshrinks
	tpSetupUpshrinks();

}}}

function tpLoadCSS() {{{
	global $context, $settings;

	$context['html_headers'] .=  "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/>";
	
    // load both stylesheets to be sure all is in, but not if things aren't setup!
	if(!empty($settings['default_theme_url']) && !empty($settings['theme_url']) && file_exists($settings['theme_dir'].'/css/tp-style.css')) {
		$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/tp-style.css?'.TPVERSION.'" />';
    }
	else {
		$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/tp-style.css?'.TPVERSION.'" />';
    }

	if(!empty($settings['default_theme_url']) && !empty($settings['theme_url']) && file_exists($settings['theme_dir'].'/css/tp-responsive.css')) {
		$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/tp-responsive.css?'.TPVERSION.'" />';
    }
	else {
		$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/tp-responsive.css?'.TPVERSION.'" />';
    }

	if(!empty($settings['default_theme_url']) && !empty($settings['theme_url']) && file_exists($settings['theme_dir'].'/css/tp-custom.css')) {
		$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/tp-custom.css?'.TPVERSION.'" />';
    }
	else {
		$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/tp-custom.css?'.TPVERSION.'" />';
    }
	
	if(!empty($context['TPortal']['padding'])) {
		$context['html_headers'] .= '
            <style type="text/css">
				.block_leftcontainer,
				.block_rightcontainer,
				.block_topcontainer,
				.block_uppercontainer,
				.block_centercontainer,
				.block_frontcontainer,
				.block_lowercontainer,
				.block_bottomcontainer {
                    padding-bottom: ' . $context['TPortal']['padding'] . 'px;
                }

                #tpleftbarHeader {
                    margin-right: ' . $context['TPortal']['padding'] . 'px;
                }

                #tprightbarHeader {
                    margin-left: ' . $context['TPortal']['padding'] . 'px;
                }

            </style>';
    }

}}}

function setupTPsettings() {{{
    global $maintenance, $context, $txt, $settings, $modSettings;

    $db = TPDatabase::getInstance();

    $context['TPortal']['always_loaded'] = array();

    // Try to load it from the cache
    if (($context['TPortal'] = cache_get_data('tpSettings', 90)) == null) {
        $context['TPortal']  = TPAdmin::getInstance()->getSetting();
        if (!empty($modSettings['cache_enable'])) {
            cache_put_data('tpSettings', $context['TPortal'], 90);
        }
    }

    // setup the userbox settings
    $userbox = explode(',', $context['TPortal']['userbox_options']);
    foreach($userbox as $u => $val) {
        $context['TPortal']['userbox'][$val] = 1;
    }

    // setup sizes for DL and articles
    $context['TPortal']['art_imagesize'] = explode(',', $context['TPortal']['art_imagesizes']);

    // another special case: sitemap items
    $context['TPortal']['sitemap'] = array();
    foreach($context['TPortal'] as $what => $value) {
        if(substr($what, 0, 14) == 'sitemap_items_' && !empty($value)) {
            $context['TPortal']['sitemap_items'] .= ','. $value;
        }
    }

    if(isset($context['TPortal']['sitemap_items'])) {
        $context['TPortal']['sitemap'] = explode(',', $context['TPortal']['sitemap_items']);
    }

    // yet another special case: category list
    $context['TPortal']['category_list'] = array();
    if(isset($context['TPortal']['cat_list'])) {
        $context['TPortal']['category_list'] = explode(',', $context['TPortal']['cat_list']);
    }

    // setup path for TP images, fallback on default theme - but not if its set already!
    if(!isset($settings['tp_images_url'])) {
        // check if the them has a folder
        if(file_exists($settings['theme_dir'].'/images/tinyportal/TParticle.png')) {
            $settings['tp_images_url'] = $settings['images_url'] . '/tinyportal';
        }
        else {
            $settings['tp_images_url'] = $settings['default_images_url'] . '/tinyportal';
        }
    }

    // hooks setting up
    $context['TPortal']['hooks'] = array(
        'topic_check' => array(),
        'board_check' => array(),
        'tp_layer' => 'tp',
        'tp_block' => 'TPblock',
    );


    // start of things
    $context['TPortal']['mystart'] = 0;
    if(isset($_GET['p']) && $_GET['p'] != '' && is_numeric($_GET['p'])) {
        $context['TPortal']['mystart'] = TPUtil::filter('p', 'get', 'int');
    }

    $context['tp_html_headers'] = '';

    // any sorting taking place?
    if(isset($_GET['tpsort'])) {
        $context['TPortal']['tpsort'] = $_GET['tpsort'];
    }
    else {
        $context['TPortal']['tpsort'] = '';
    }

    require_once(SOURCEDIR . '/TPSubs.php'); 

    // if not in forum start off empty
    $context['TPortal']['is_front'] = false;
    $context['TPortal']['is_frontpage'] = false;
    if(!isset($_GET['action']) && !isset($_GET['board']) && !isset($_GET['topic'])) {
        TPstrip_linktree();
        // a switch to make it clear what is "forum" and not
        $context['TPortal']['not_forum'] = true;
    }
    // are we actually on frontpage then?
    if(!isset($_GET['cat']) && !isset($_GET['page']) && !isset($_GET['action'])) {
        $context['TPortal']['is_front'] = true;
        $context['TPortal']['is_frontpage'] = true;
    }

    // Set the page title.
    if($context['TPortal']['is_front'] && !empty($context['TPortal']['frontpage_title'])) {
        $context['page_title'] = $context['TPortal']['frontpage_title'];
    }

    if(isset($_GET['action']) && $_GET['action'] == 'tpadmin') {
        $context['page_title'] = $context['forum_name'] . ' - ' . $txt['tp-admin'];
    }

    // if we are in maintance mode, just hide panels
    if (!empty($maintenance) && !allowedTo('admin_forum')) {
        tp_hidebars('all');
    }

    // save the action value
    $context['TPortal']['action'] = !empty($_GET['action']) ? TPUtil::filter('action', 'get', 'string') : '';

    // save the frontapge setting for ELK
    $settings['TPortal_front_type'] = $context['TPortal']['front_type'];
    if(empty($context['page_title'])) {
        $context['page_title'] = $context['forum_name'];
    }

}}}

// TPortal side bar, left or right.
function TPortal_panel($side) {{{
	global $context, $scripturl, $settings;

	// decide for $flow
	$flow = $context['TPortal']['block_layout_' . $side];

	$panelside = $paneltype = ($side == 'front' ? 'frontblocks' : 'blocks');

	// set the grid type
	if($flow == 'grid') {
		$grid_selected = $context['TPortal']['blockgrid_' . $side];
		if($grid_selected == 'colspan3') {
			$grid_recycle = 4;
        }
		elseif($grid_selected == 'rowspan1') {
			$grid_recycle = 5;
        }

		$grid_entry = 0;
		// fetch the grids..
		TP_blockgrids();
	}

	// check if we left out the px!!
	if(is_numeric($context['TPortal']['blockwidth_'.$side])) {
		$context['TPortal']['blockwidth_'.$side] .= 'px';
    }

	// for the cols, calculate numbers
	if($flow == 'horiz2') {
		$flowgrid = array(
			'1' => array(1, 0),
			'2' => array(1, 1),
			'3' => array(2, 1),
			'4' => array(2, 2),
			'5' => array(3, 2),
			'6' => array(3, 3),
			'7' => array(4, 3),
			'8' => array(4, 4),
			'9' => array(5, 4),
			'10' => array(5, 5),
			'11' => array(6, 5),
			'12' => array(6, 6),
			'13' => array(7, 6),
			'14' => array(7, 7),
			'15' => array(8, 7),
			'16' => array(8, 8),
		);
	}
	elseif($flow == 'horiz3') {
		$flowgrid = array(
			'1' => array(1, 0, 0),
			'2' => array(1, 1, 0),
			'3' => array(1, 1, 1),
			'4' => array(2, 1, 1),
			'5' => array(2, 2, 1),
			'6' => array(2, 2, 2),
			'7' => array(3, 2, 2),
			'8' => array(3, 3, 2),
			'9' => array(3, 3, 3),
			'10' => array(4, 3, 3),
			'11' => array(4, 4, 3),
			'12' => array(4, 4, 4),
			'13' => array(5, 4, 4),
			'14' => array(5, 5, 4),
			'15' => array(5, 5, 5),
			'16' => array(6, 5, 5),
		);
	}
	elseif($flow == 'horiz4') {
		$flowgrid = array(
			'1' => array(1, 0, 0, 0),
			'2' => array(1, 1, 0, 0),
			'3' => array(1, 1, 1, 0),
			'4' => array(1, 1, 1, 1),
			'5' => array(2, 1, 1, 1),
			'6' => array(2, 2, 1, 1),
			'7' => array(2, 2, 2, 1),
			'8' => array(2, 2, 2, 2),
			'9' => array(3, 2, 2, 2),
			'10' => array(3, 3, 2, 2),
			'11' => array(3, 3, 3, 2),
			'12' => array(3, 3, 3, 3),
			'13' => array(4, 3, 3, 3),
			'14' => array(4, 4, 3, 3),
			'15' => array(4, 4, 4, 3),
			'16' => array(4, 4, 4, 4),
		);
	}

	if(in_array($flow, array('horiz2', 'horiz3', 'horiz4'))) {
		$pad = $context['TPortal']['padding'];
        switch($flow) {
            case 'horiz2':
			    $wh = 50;
                break;
            case 'horiz3':
			    $wh = 33;
                break;
            case 'horiz4':
			    $wh = 25;
                break;
        }
		echo '<div style="width:100%;"><div class="panelsColumns" style="' . (isset($wh) ? 'width: '.$wh.'%;' : '' ) . 'padding-right: '.$pad.'px;float:left;">';
	}
	$flowmain = 0;
	$flowsub = 0;
	$bcount = 0;
	$flowcount = isset($context['TPortal'][$panelside][$side]) ? count($context['TPortal'][$panelside][$side]) : 0;
	if(!isset($context['TPortal'][$panelside][$side])) {
		$context['TPortal'][$panelside][$side] = array();
    }

	$n = count($context['TPortal'][$paneltype][$side]);
	$context['TPortal'][$panelside][$side] = (array) $context['TPortal'][$panelside][$side];
	foreach ($context['TPortal'][$panelside][$side] as $i => &$block) {
		if(!isset($block['frame'])) {
			continue;
        }

		$theme = $block['frame'] == 'theme';

		// check if a language title string exists
		$newtitle = TPgetlangOption($block['lang'], $context['user']['language']);
		if(!empty($newtitle)) {
			$block['title'] = $newtitle;
        }

		$use = true;
		// special title links and variables for special types
		switch($block['type']){
			case 'searchbox':
				$mp = '<a class="subject" href="'.$scripturl.'?action=search">'.$block['title'].'</a>';
				$block['title'] = $mp;
				break;
			case 'onlinebox':
				$mp = '<a class="subject"  href="'.$scripturl.'?action=who">'.$block['title'].'</a>';
				$block['title'] = $mp;
				if($block['var1'] == 0) {
					$context['TPortal']['useavataronline'] = 0;
                }
				else {
					$context['TPortal']['useavataronline'] = 1;
                }
				break;
			case 'userbox':
				if($context['user']['is_logged']) {
					$mp = ''.$block['title'].'';
                }
				else {
					$mp = '<a class="subject"  href="'.$scripturl.'?action=login">'.$block['title'].'</a>';
                }
				$block['title'] = $mp;
				break;
			case 'statsbox':
				$mp='<a class="subject"  href="'.$scripturl.'?action=stats">'.$block['title'].'</a>';
				$block['title'] = $mp;
				break;
			case 'recentbox':
				$mp = '<a class="subject"  href="'.$scripturl.'?action=recent">'.$block['title'].'</a>';
				$context['TPortal']['recentboxnum'] = $block['body'];
				$context['TPortal']['useavatar'] = $block['var1'];
				$context['TPortal']['boardmode'] = $block['var3'];
				if($block['var1'] == '') {
					$context['TPortal']['useavatar'] = 1;
                }
				if(!empty($block['var2'])) {
					$context['TPortal']['recentboards'] = explode(',', $block['var2']);
                }
				break;
			case 'scriptbox':
				$block['title'] = '<span class="header">' . $block['title'] . '</span>';
				$context['TPortal']['scriptboxbody'] = $block['body'];
				break;
			case 'phpbox':
				$block['title']='<span class="header">' . $block['title'] . '</span>';
				$context['TPortal']['phpboxbody'] = $block['body'];
				break;
			case 'ssi':
				$block['title'] = '<span class="header">' . $block['title'] . '</span>';
				$context['TPortal']['ssifunction'] = $block['body'];
				break;
			case 'module':
				$block['title'] = '<span class="header">' . $block['title'] . '</span>';
				$context['TPortal']['moduleblock'] = $block['body'];
				$context['TPortal']['modulevar2'] = $block['var2'];
				break;
			case 'themebox':
				$block['title'] = '<span class="header">' . $block['title'] . '</span>';
				$context['TPortal']['themeboxbody'] = $block['body'];
				break;
			case 'newsbox':
				$block['title'] = '<span class="header">' . $block['title'] . '</span>';
				if($context['random_news_line'] == '') {
					$use = false;
                }
				break;
			case 'articlebox':
				$block['title'] = '<span class="header">' . $block['title'] . '</span>';
				$context['TPortal']['blockarticle'] = $block['body'];
				break;
			case 'rss':
				$block['title'] = '<span class="header rss">' . $block['title'] . '</span>';
				$context['TPortal']['rss'] = $block['body'];
				$context['TPortal']['rss_notitles'] = $block['var2'];
				$context['TPortal']['rss_utf8'] = $block['var1'];
				$context['TPortal']['rsswidth'] = isset($block['var3']) ? $block['var3'] : '';
				$context['TPortal']['rssmaxshown'] = !empty($block['var4']) ? $block['var4'] : '20';
				break;
			case 'categorybox':
				$block['title'] = '<span class="header">' . $block['title'] . '</span>';
				$context['TPortal']['blocklisting'] = $block['body'];
				$context['TPortal']['blocklisting_height'] = $block['var1'];
				$context['TPortal']['blocklisting_author'] = $block['var2'];
				break;
			case 'shoutbox':
            	$block['title'] = '<span class="header">' . $block['title'] . '</span>';
				$context['TPortal']['shoutbox_stitle'] = $block['body'];
				$context['TPortal']['shoutbox_id'] = $block['var2'];
				$context['TPortal']['shoutbox_layout'] = $block['var3'];
				$context['TPortal']['shoutbox_height'] = $block['var4'];
            case 'modulebox':
            	$block['title'] = '<span class="header">' . $block['title'] . '</span>';
				$context['TPortal']['moduleid'] = $block['var1'];
				$context['TPortal']['modulevar2'] = $block['var2'];
				$context['TPortal']['modulebody'] = $block['body'];
				break;
			case 'catmenu':
				$block['title'] = '<span class="header">' . $block['title'] . '</span>';
				$context['TPortal']['menuid'] = is_numeric($block['body']) ? $block['body'] : 0;
				$context['TPortal']['menuvar1'] = $block['var1'];
				$context['TPortal']['menuvar2'] = $block['var2'];
				$context['TPortal']['blockid'] = $block['id'];
				break;
		}

		// render them horisontally
		if($flow == 'horiz') {
			$pad = $context['TPortal']['padding'];
			if($i == ($flowcount-1)) {
				$pad=0;
            }
			echo '<div class="panelsColumnsHorizontally" style="float: left; width: ' . $context['TPortal']['blockwidth_'.$side].';"><div style="padding-right: ' . $pad . 'px;">';
			call_user_func($context['TPortal']['hooks']['tp_block'], $block, $theme, $side);
			echo '</div></div>';
		}
		// render them horisontally
		elseif(in_array($flow, array('horiz2', 'horiz3', 'horiz4'))) {
			$pad = $context['TPortal']['padding'];
			if($flow == 'horiz2') {
				$wh = 50;
			}
			elseif($flow == 'horiz3') {
					$wh = 33;
			}
			elseif($flow == 'horiz4') {
				$wh = 25;
            }

			if(isset($flowgrid) && $flowsub == $flowgrid[$flowcount][$flowmain]) {
				$flowsub = 0;
				$flowmain++;
				if($flow == 'horiz2' && $flowmain == 1) {
                    $pad = 0;
                }
				elseif($flow == 'horiz3' && $flowmain == 2) {
                    $pad = 0;
                    $wh = 34;
                }
                elseif($flow == 'horiz4' && $flowmain == 3) {
                    $pad = 0;
                }
				echo '</div><div class="panelsColumns" style="' . (isset($wh) ? 'width: '. $wh.'%;' : '') .  'padding-right: '.$pad.'px;float:left;">';
			}
			call_user_func($context['TPortal']['hooks']['tp_block'], $block, $theme, $side);
		}
		// according to a grid
		elseif($flow == 'grid') {
			echo TP_blockgrid($block, $theme, $grid_entry, $side, $grid_entry == ($grid_recycle - 1) ? true : false, $grid_selected);
			$grid_entry++;
			if($grid_recycle == $grid_entry) {
				$grid_entry = 0;
            }
			// what if its the last block, but in the middle of the recycle?
			if($i == $n - 1) {
				if($grid_entry > 0) {
					for($a = $grid_entry; $a < $grid_recycle; $a++) {
						echo TP_blockgrid(0, 0, $a, $side, $a == ($grid_recycle-1) ? true : false, $grid_selected,true);
                    }
				}
			}
		}
		// or just plain vertically
		else {
			call_user_func($context['TPortal']['hooks']['tp_block'], $block, $theme, $side);
        }

		$bcount++;
		$flowsub++;
	}
	if(in_array($flow, array('horiz2', 'horiz3', 'horiz4'))) {
		echo '</div><p class="clearthefloat"></p></div>';
    }

	// the upshrink routine for blocks
	// echo '</div>
		echo '<script type="text/javascript"><!-- // --><![CDATA[
				function toggle( targetId )
				{
					var state = 0;
					var blockname = "block" + targetId;
					var blockimage = "blockcollapse" + targetId;

					if ( document.getElementById ) {
						target = document.getElementById( blockname );
						if ( target.style.display == "none" ) {
							target.style.display = "";
							state = 1;
						}
						else {
							target.style.display = "none";
							state = 0;
						}

						document.getElementById( blockimage ).src = "'.$settings['tp_images_url'].'" + (state ? "/TPcollapse.png" : "/TPexpand.png");
						var tempImage = new Image();
						tempImage.src = "'.$scripturl.'?action=tportal;sa=upshrink;id=" + targetId + ";state=" + state + ";" + (new Date().getTime());

					}
				}
			// ]]></script>';

	// return $code;
}}}

function tpSetupUpshrinks() {{{
	global $context, $settings;

    $db = TPDatabase::getInstance();

	$context['tp_panels'] = array();
	if(isset($_COOKIE['tp_panels'])){
		$shrinks = explode(',', $_COOKIE['tp_panels']);
		foreach($shrinks as $sh => $val) {
			$context['tp_panels'][] = $val;
        }
	}

	// the generic panel upshrink code
	$context['html_headers'] .= '
	  <script type="text/javascript"><!-- // --><![CDATA[
		' . (count($context['tp_panels']) > 0 ? '
		var tpPanels = new Array(\'' . (implode("','",$context['tp_panels'])) . '\');' : '
		var tpPanels = new Array();') . '
		function togglepanel( targetID )
		{
			var pstate = 0;
			var panel = targetID;
			var img = "toggle_" + targetID;
			var ap = 0;

			if ( document.getElementById && (0 !== panel.length) ) {
				target = document.getElementById( panel );
                if ( target !== null ) {
                    if ( target.style.display == "none" ) {
                        target.style.display = "";
                        pstate = 1;
                        removeFromArray(targetID, tpPanels);
                        document.cookie="tp_panels=" + tpPanels.join(",") + "; expires=Wednesday, 01-Aug-2040 08:00:00 GMT";
                        var image = document.getElementById(img);
                        if(image !== null) {
                            image.src = \'' . $settings['tp_images_url'] . '/TPupshrink.png\';
                        }
                    }
                    else {
                        target.style.display = "none";
                        pstate = 0;
                        tpPanels.push(targetID);
                        document.cookie="tp_panels=" + tpPanels.join(",") + "; expires=Wednesday, 01-Aug-2040 08:00:00 GMT";
                        var image = document.getElementById(img);
                        if(image !== null) {
                            image.src = \'' . $settings['tp_images_url'] . '/TPupshrink2.png\';
                        }
                    }
                }
			}
		}
		function removeFromArray(value, array){
			for(var x=0;x<array.length;x++){
				if(array[x]==value){
					array.splice(x, 1);
				}
			}
			return array;
		}
		function inArray(value, array){
			for(var x=0;x<array.length;x++){
				if(array[x]==value){
					return 1;
				}
			}
			return 0;
		}
	// ]]></script>';

	$panels = array('Left', 'Right', 'Top', 'Center', 'Lower', 'Bottom');
	$context['TPortal']['upshrinkpanel'] = '';

	if($context['TPortal']['showcollapse'] == 1) {
		foreach($panels as $pa => $pan) {
			$side = strtolower($pan);
			if($context['TPortal'][$side.'panel'] == 1) {
				// add to the panel
				if($pan == 'Left' || $pan == 'Right') {
					$context['TPortal']['upshrinkpanel'] .= tp_hidepanel2('tp' . strtolower($pan) . 'barHeader', 'tp' . strtolower($pan) . 'barContainer', strtolower($pan).'-tp-upshrink_description');
                }
				else {
					$context['TPortal']['upshrinkpanel'] .= tp_hidepanel2('tp' . strtolower($pan) . 'barHeader', '', strtolower($pan).'-tp-upshrink_description');
                }
			}
		}
	}

	// get user values
	if($context['user']['is_logged']) {
		// set some values based on user-prefs
		$result = $db->query('', '
			SELECT type, value, item
			FROM {db_prefix}tp_data
			WHERE type = {int:type}
			AND id_member = {int:id_mem}',
			array('type' => 2, 'id_mem' => $context['user']['id'])
		);

		if($db->num_rows($result) > 0) {
			while($row = $db->fetch_assoc($result)) {
				$context['TPortal']['usersettings']['wysiwyg'] = $row['value'];
			}
			$db->free_result($result);
		}
		$context['TPortal']['use_wysiwyg']  = (int) $context['TPortal']['use_wysiwyg'];
		$context['TPortal']['show_wysiwyg'] = $context['TPortal']['use_wysiwyg'];

		if ($context['TPortal']['use_wysiwyg'] > 0) {
			$context['TPortal']['allow_wysiwyg'] = true;
			if (isset($context['TPortal']['usersettings']['wysiwyg'])) {
				$context['TPortal']['show_wysiwyg'] = (int) $context['TPortal']['usersettings']['wysiwyg'];
			}
		}
		else {
			$context['TPortal']['show_wysiwyg'] = $context['TPortal']['use_wysiwyg'];
			$context['TPortal']['allow_wysiwyg'] = false;
		}

		// check that we are not in admin section
		if((isset($_GET['action']) && $_GET['action'] == 'tpadmin') && ((isset($_GET['sa']) && $_GET['sa'] == 'settings') || !isset($_GET['sa']))) {
			$in_admin = true;
        }
	}

	// get the cookie for upshrinks
	$context['TPortal']['upshrinkblocks'] = array();
	if(isset($_COOKIE['tp-upshrinks'])) {
		$shrinks = explode(',', $_COOKIE['tp-upshrinks']);
		foreach($shrinks as $sh => $val) {
			$context['TPortal']['upshrinkblocks'][] = $val;
        }
	}

	return;

}}}

function TP_blockgrid($block, $theme, $pos, $side, $last = false, $gridtype, $none = false) {{{
	global $context;

	// first, set the table, equal in all grids
	if($pos == 0) {
		echo '<div style="width:100%;">';
    }

	if(isset($context['TPortal']['grid'][$gridtype][$pos]['doubleheight'])) {
		$dh = true;
    }
	else {
		$dh = false;
    }

	// render if its not empty
	if($none == false) {
		echo $context['TPortal']['grid'][$gridtype][$pos]['before'] , call_user_func($context['TPortal']['hooks']['tp_block'], $block, $theme, $side, $dh) , $context['TPortal']['grid'][$gridtype][$pos]['after'];
    }
	else {
		echo $context['TPortal']['grid'][$gridtype][$pos]['before'] . '&nbsp;' . $context['TPortal']['grid'][$gridtype][$pos]['after'];
    }

	// last..if its the last block,close the table
	if($last) {
		echo '<p class="clearthefloat"></p></div>';
    }

}}}

function TP_blockgrids() {{{
	global $context;

	$context['TPortal']['grid'] = array();
	$context['TPortal']['grid']['colspan3'][0] = array('before' => '<div class="gridColumns">', 'after' => '</div>');
	$context['TPortal']['grid']['colspan3'][1] = array('before' => '<div><div class="gridColumns" style="width:32.3%;padding-right:0.7%;float:left;">', 'after' => '</div>');
	$context['TPortal']['grid']['colspan3'][2] = array('before' => '<div class="gridColumns" style="width:32.3%;padding-right:0.7%;float:left;">', 'after' => '</div>');
	$context['TPortal']['grid']['colspan3'][3] = array('before' => '<div class="gridColumns" style="width:34%;float:left;">', 'after' => '</div><p class="clearthefloat"></p></div>');

	$context['TPortal']['grid']['rowspan1'][0] = array('before' => '<div class="gridC" style="width:32.3%;padding-right: 0.7%;float:left;">', 'after' => '</div>', 'doubleheight' => true);
	$context['TPortal']['grid']['rowspan1'][1] = array('before' => '<div class="gridC" style="width:67%;float:left;"><div class="gridColumns" style="width:49%;padding-right: 1%;padding-bottom: 5px;float:left;">', 'after' => '</div>');
	$context['TPortal']['grid']['rowspan1'][2] = array('before' => '<div class="gridColumns" style="width:50%;float:left;">', 'after' => '</div>');
	$context['TPortal']['grid']['rowspan1'][3] = array('before' => '<div class="gridColumns" style="width:49%;padding-right: 1%;float:left;">', 'after' => '</div>');
	$context['TPortal']['grid']['rowspan1'][4] = array('before' => '<div class="gridColumns" style="width:50%;float:left;">', 'after' => '</div><p class="clearthefloat"></p></div>');

}}}

// TPortal leftblocks
function TPortal_leftbar() {{{
	TPortal_sidebar('left');
}}}

// TPortal centerbar
function TPortal_centerbar() {{{
	TPortal_sidebar('center');
}}}

// TPortal rightbar
function TPortal_rightbar() {{{
	TPortal_sidebar('right');
}}}

?>
