<?php
/**
 * @package TinyPortal
 * @version 1.0.0 RC1
 * @author TinyPortal - http://www.tinyportal.net
 * @license BSD 3.0 http://opensource.org/licenses/BSD-3-Clause/
 *
 * Copyright (C) 2020 - The TinyPortal Team
 *
 */
use \TinyPortal\Model\Article as TPArticle;
use \TinyPortal\Model\Block as TPBlock;
use \TinyPortal\Model\Category as TPCategory;
use \TinyPortal\Model\Database as TPDatabase;
use \TinyPortal\Model\Util as TPUtil;

if (!defined('ELK')) {
        die('Hacking attempt...');
}

function TPBlockInit() {{{
	global $context, $txt;

	if(loadLanguage('TPmodules') == false) {
		loadLanguage('TPmodules', 'english');
    }

	if(loadLanguage('TPortalAdmin') == false) {
		loadLanguage('TPortalAdmin', 'english');
    }

	// a switch to make it clear what is "forum" and not
	$context['TPortal']['not_forum'] = true;

	// call the editor setup
	require_once(SUBSDIR . '/TPortal.subs.php');

	// clear the linktree first
	TPstrip_linktree();

}}}

function TPBlockActions(&$subActions) {{{

   $subActions = array_merge(
        array (
            'showblock'      => array('TPBlock.php', 'showBlock',   array()),
        ),
        $subActions
    );

}}}

function getBlocks() {{{

	global $context, $scripturl, $user_info, $modSettings;

    $tpBlock    = TPBlock::getInstance();

	$now = time();
	// setup the containers
	$blocks = $tpBlock->getBlockType(); 


	$context['TPortal']['hide_frontbar_forum'] = 0;

	$fetch_articles = array();
	$fetch_article_titles = array();

    $count  = array_flip($tpBlock->getBlockPanel());
    foreach($count as $k => $v) {
        $count[$k] = 0;
    }

	$panels             = $tpBlock->getBlockBar(); 
    $availableBlocks    = $tpBlock->getBlockPermissions();
	if (is_array($availableBlocks) && count($availableBlocks)) {
        foreach($availableBlocks as $row) { 
			// some tests to minimize sql calls
			if($row['type'] == TP_BLOCK_THEMEBOX) {
				$test_themebox = true;
            }
			elseif($row['type'] == TP_BLOCK_ARTICLEBOX) {
				$test_articlebox = true;
				if(is_numeric($row['body'])) {
					$fetch_articles[] = $row['body'];
                }
			}
            elseif($row['type'] == TP_BLOCK_CATEGORYBOX) {
				$test_catbox = true;
				if(is_numeric($row['body'])) {
					$fetch_article_titles[] = $row['body'];
                }
			}
            elseif($row['type'] == TP_BLOCK_SHOUTBOX) {
                call_integration_hook('integrate_tp_shoutbox', array(&$row));
            }

            // decode the block settings
            $set        = json_decode($row['settings'], true);
			$can_edit   = !empty($row['editgroups']) ? get_perm($row['editgroups'],'') : false;
			$can_manage = allowedTo('tp_blocks');
			if($can_manage) {
				$can_edit = false;
            }
			$blocks[$panels[$row['bar']]][$count[$panels[$row['bar']]]] = array(
				'frame'     => $row['frame'],
				'title'     => strip_tags($row['title'], '<center>'),
                'type'      => $tpBlock->getBlockType($row['type']),
				'body'      => $row['body'],
				'visible'   => $row['visible'],
                'settings'  => $row['settings'],
				'var1'      => $set['var1'],
				'var2'      => $set['var2'],
				'var3'      => $set['var3'],
				'var4'      => $set['var4'],
				'var5'      => $set['var5'],
				'id'        => $row['id'],
				'lang'      => $row['lang'],
				'display'   => $row['display'],
				'can_edit'  => $can_edit,
				'can_manage' => $can_manage,
			);
			$count[$panels[$row['bar']]]++;
		}
	}

    // if a block displays an article
    if(isset($test_articlebox)) {
		$context['TPortal']['blockarticles'] = array();
        $tpArticle  = new TPArticle();
        $articles   = $tpArticle->getArticle($fetch_articles);
        if(is_array($articles)) {
            foreach($articles as $article) {
				// allowed and all is well, go on with it.
				$context['TPortal']['blockarticles'][$article['id']] = $article;
				// setup the avatar code
				if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize') {
					$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
					$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
				}
				else {
					$avatar_width = '';
					$avatar_height = '';
				}
                $context['TPortal']['blockarticles'][$article['id']]['avatar'] = determineAvatar( array(      
                            'avatar'            => $article['avatar'],
                            'email_address'     => $article['email_address'],
                            'filename'          => !empty($article['filename']) ? $article['filename'] : '',
                            'id_attach'         => $article['id_attach'],
                            'attachment_type'   => $article['attachment_type'],
                        )
                )['image'];
				// sort out the options
				$context['TPortal']['blockarticles'][$article['id']]['visual_options'] = array();
				// since these are inside blocks, some stuff has to be left out
				$context['TPortal']['blockarticles'][$article['id']]['frame'] = 'none';
			}
		}
	}

    // any cat listings from blocks?
    if(isset($test_catbox)) {
        $tpArticle  = new TPArticle();
        $categories = $tpArticle->getArticlesInCategory($fetch_article_titles, false, true);
        if (!isset($context['TPortal']['blockarticle_titles'])) {
			$context['TPortal']['blockarticle_titles'] = array();
        }
        if(is_array($categories)) {
            foreach($categories as $row) {
                if(empty($row['author'])) {
                    global $memberContext;
                    // Load their context data.
                    if(!array_key_exists('admin_features', $context)) {
                        $context['admin_features']  = array();
                        $adminFeatures              = true;
                    }
                    else {
                        $adminFeatures              = false;
                    }

                    \loadMemberData($row['author_id'], false, 'normal');
                    \loadMemberContext($row['author_id']);

                    if($adminFeatures == true) {
                        unset($context['admin_features']);
                    }
                    $row['real_name'] = $memberContext[$row['author_id']]['username'];
                }
                else {
                    $row['real_name'] = $row['author'];
                }
				$context['TPortal']['blockarticle_titles'][$row['category']][$row['date'].'_'.$row['id']] = array(
					'id' => $row['id'],
					'subject' => $row['subject'],
					'shortname' => $row['shortname']!='' ?$row['shortname'] : $row['id'] ,
					'category' => $row['category'],
					'poster' => '<a href="'.$scripturl.'?action=profile;u='.$row['author_id'].'">'.$row['real_name'].'</a>',
				);
			}
		}
    }

	// for tpadmin
	$context['TPortal']['adminleftpanel']   = $context['TPortal']['leftpanel'];
	$context['TPortal']['adminrightpanel']  = $context['TPortal']['rightpanel'];
	$context['TPortal']['admincenterpanel'] = $context['TPortal']['centerpanel'];
	$context['TPortal']['adminbottompanel'] = $context['TPortal']['bottompanel'];
	$context['TPortal']['admintoppanel']    = $context['TPortal']['toppanel'];
	$context['TPortal']['adminlowerpanel']  = $context['TPortal']['lowerpanel'];

	// if admin specifies no blocks, no blocks are shown! likewise, if in admin or tpadmin screen, turn off blocks
	if (in_array($context['TPortal']['action'], array('moderate', 'theme', 'tpadmin', 'admin', 'ban', 'boardrecount', 'cleanperms', 'detailedversion', 'dumpdb', 'featuresettings', 'featuresettings2', 'findmember', 'maintain', 'manageattachments', 'manageboards', 'managecalendar', 'managesearch', 'membergroups', 'modlog', 'news', 'optimizetables', 'packageget', 'packages', 'permissions', 'pgdownload', 'postsettings', 'regcenter', 'repairboards', 'reports', 'serversettings', 'serversettings2', 'smileys', 'viewErrorLog', 'viewmembers'))) {
	    $in_admin = true;
    }

	if(($context['user']['is_admin'] && isset($_GET['noblocks'])) || ($context['TPortal']['hidebars_admin_only']=='1' && isset($in_admin))) {
		tp_hidebars();
    }

	// check the panels
	foreach($panels as $p => $panel) {
		// any blocks at all?
		if($count[$panel] < 1) {
			$context['TPortal'][$panel.'panel'] = 0;
        }
		// check the hide setting
		if(!isset($context['TPortal']['not_forum']) && $context['TPortal']['hide_' . $panel . 'bar_forum']==1) {
			tp_hidebars($panel);
        }
	}

	$context['TPortal']['blocks'] = $blocks;

}}}


// Admin Actions
function TPBlockAdminActions(&$subActions) {{{

   $subActions = array_merge(
        array (
            'editblock'      => array('TPBlock.php', 'editBlock',   array()),
            'deleteblock'    => array('TPBlock.php', 'deleteBlock', array()),
            'saveblock'      => array('TPBlock.php', 'saveBlock',   array()),
        ),
        $subActions
    );

}}}

function adminBlocks() {{{
	global $context, $txt, $settings, $scripturl;

	isAllowedTo('tp_blocks');
    
    $tpBlock    = TPBlock::getInstance();

	if(($context['TPortal']['subaction']=='blocks') && !isset($_GET['overview'])) {
		TPadd_linktree($scripturl.'?action=admin;area=tpblocks;sa=blocks', $txt['tp-blocks']);
	}
	
	if(isset($_GET['addblock'])) {
		TPadd_linktree($scripturl.'?action=admin;area=tpblocks;sa=addblock', $txt['tp-addblock']);
		// collect all available PHP block snippets
		$context['TPortal']['blockcodes']   = TPcollectSnippets();
		$context['TPortal']['copyblocks']   = $tpBlock->getBlocks();
	}

	// Move the block up or down in the panel list of blocks
	if(isset($_GET['addpos']) || isset($_GET['subpos'])) {
		checksession('get');
	    if(isset($_GET['addpos'])) {
		    $id         = is_numeric($_GET['addpos']) ? $_GET['addpos'] : 0;
            $current    = $tpBlock->getBlockData(array( 'pos', 'bar'), array( 'id' => $id) );
            $new        = $current[0]['pos'] + 1;
            $existing   = $tpBlock->getBlockData('id', array( 'bar' => $current[0]['bar'], 'pos' => $new ) );
            if(is_array($existing)) {
                $tpBlock->updateBlock($existing[0]['id'], array( 'pos' => $current[0]['pos']));
            }
        } 
        else {
		    $id         = is_numeric($_GET['subpos']) ? $_GET['subpos'] : 0;
            $current    = $tpBlock->getBlockData(array( 'pos', 'bar'), array( 'id' => $id) );
            $new        = $current[0]['pos'] - 1;
            $existing   = $tpBlock->getBlockData('id', array( 'bar' => $current[0]['bar'], 'pos' => $new ) );
            if(is_array($existing)) {
                $tpBlock->updateBlock($existing[0]['id'], array( 'pos' => $current[0]['pos']));
            }
        }
        $tpBlock->updateBlock($id, array( 'pos' => $new));
		redirectexit('action=admin;area=tpblocks;sa=blocks');
	}

	// change the on/off
	if(isset($_GET['blockon'])) {
		checksession('get');
		$id         = is_numeric($_GET['blockon']) ? $_GET['blockon'] : 0;
        $current    = $tpBlock->getBlockData(array( 'off' ), array( 'id' => $id) );
        if(is_array($current)) {
            if($current[0]['off'] == 1) {
                $tpBlock->updateBlock($id, array( 'off' => '0' ));
            }
            else {
                $tpBlock->updateBlock($id, array( 'off' => '1' ));
            }
        }
        redirectexit('action=admin;area=tpblocks;sa=blocks');
	}

	// remove it?
	if(isset($_GET['blockdelete'])) {
		checksession('get');
		$id         = is_numeric($_GET['blockdelete']) ? $_GET['blockdelete'] : 0;
        $tpBlock->deleteBlock($id);
		redirectexit('action=admin;area=tpblocks;sa=blocks');
	}
   
    foreach( array ( 'blockright', 'blockleft', 'blockcenter', 'blockfront', 'blockbottom', 'blocktop', 'blocklower') as $block_location ) {
        if(array_key_exists($block_location, $_GET)) {
            checksession('get');
            $id     = is_numeric($_GET[$block_location]) ? $_GET[$block_location] : 0;
            $loc    = $tpBlock->getBlockBarId(str_replace('block', '', $block_location));
            $tpBlock->updateBlock($id, array( 'bar' => $loc ));
            redirectexit('action=admin;area=tpblocks;sa=blocks');
        }
	}

	// are we on overview screen?
	if(isset($_GET['overview'])) {
		TPadd_linktree($scripturl.'?action=admin;area=tpblocks;sa=blocks;overview', $txt['tp-blockoverview']);
		
		// fetch all blocks member group permissions
        $data   = $tpBlock->getBlockData(array('id', 'title', 'bar', 'access', 'type'), array( 'off' => 0 ) );
		if(is_array($data)) {
			$context['TPortal']['blockoverview'] = array();
            foreach($data as $row) {
				$context['TPortal']['blockoverview'][] = array(
					'id' => $row['id'],
					'title' => $row['title'],
					'bar' => $row['bar'],
					'type' => $row['type'],
					'access' => explode(',', $row['access']),
				);
			}
		}
		get_grps(true,true);
	}

	// or maybe adding it?
	if(isset($_GET['addblock'])) {
		get_articles();
		// check which side its mean to be on
		$context['TPortal']['blockside'] = $_GET['addblock'];
	}

	if($context['TPortal']['subaction']=='panels') {
		TPadd_linktree($scripturl.'?action=admin;area=tpblocks;sa=panels', $txt['tp-panels']);
    }
	
	else {
		foreach($tpBlock->getBlockPanel() as $p => $pan) {
			if(isset($_GET[$pan])) {
				$context['TPortal']['panelside'] = $pan;
            }
		}
        $bars   = $tpBlock->getBlockBar();
        $blocks = $tpBlock->getBlocks();
		if (is_countable($blocks) && count($blocks) > 0) {
            $bar    = array_column($blocks, 'bar');
            $pos    = array_column($blocks, 'pos');
            if(array_multisort($bar, SORT_ASC, $pos, SORT_ASC, $blocks)) {
                foreach($blocks as $row) {
                    // decode the block settings
                    $set = json_decode($row['settings'], true);
                    $context['TPortal']['admin_'.$bars[$row['bar']].'block']['blocks'][] = array(
                        'frame' => $row['frame'],
                        'title' => $row['title'],
                        'type' => $tpBlock->getBlockType($row['type']),
                        'body' => $row['body'],
                        'id' => $row['id'],
                        'access' => $row['access'],
                        'pos' => $row['pos'],
                        'off' => $row['off'],
                        'visible' => $row['visible'],
                        'var1' => $set['var1'],
                        'var2' => $set['var2'],
                        'lang' => $row['lang'],
                        'display' => $row['display'],
                        'loose' => $row['display'] != '' ? true : false,
                        'editgroups' => $row['editgroups']
                    );
                }
            }
		}
	}
	get_articles();

	$context['html_headers'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url']. '/scripts/editor.js?fin20"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		function getXMLHttpRequest()
		{
			if (window.XMLHttpRequest)
				return new XMLHttpRequest;
			else if (window.ActiveXObject)
				return new ActiveXObject("MICROSOFT.XMLHTTP");
			else
				alert("Sorry, but your browser does not support Ajax");
		}
		window.onload = startToggle;
		function startToggle()
		{
			var img = document.getElementsByTagName("img");
			for(var i = 0; i < img.length; i++)
			{
				if (img[i].className == "toggleButton")
					img[i].onclick = toggleBlock;
			}
		}
		function toggleBlock(e)
		{
			var e = e ? e : window.event;
			var target = e.target ? e.target : e.srcElement;
			while(target.className != "toggleButton")
				  target = target.parentNode;
			var id = target.id.replace("blockonbutton", "");
			var Ajax = getXMLHttpRequest();
			Ajax.open("POST", "?action=admin;area=tpblocks;blockon=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'");
			Ajax.setRequestHeader("Content-type", "application/x-www-form-urlencode");
			var source = target.src;
			target.src = "' . $settings['tp_images_url'] . '/ajax.gif"
			Ajax.onreadystatechange = function()
			{
				if(Ajax.readyState == 4)
				{
					target.src = source == "' . $settings['tp_images_url'] . '/TPactive1.png" ? "' . $settings['tp_images_url'] . '/TPactive2.png" : "' . $settings['tp_images_url'] . '/TPactive1.png";
				}
			}
			var params = "?action=admin;area=tpblocks;blockon=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'";
			Ajax.send(params);
		}
	// ]]></script>';

}}}

function editBlock( $block_id = 0 ) {{{

	global $settings, $context, $scripturl, $txt;

    $tpBlock    = TPBlock::getInstance();
    $db         = TPDatabase::getInstance();

    if(empty($block_id)) {
	    $block_id  = TPUtil::filter('id', 'get', 'int');
    }

    if(!is_numeric($block_id)) {
        fatal_error($txt['tp-notablock'], false);
    }

    if(loadLanguage('TPortalAdmin') == false) {
        loadLanguage('TPortalAdmin', 'english');
    }

	checksession('get');

    require_once(SOURCEDIR.'/TPortalAdmin.php');

	TPadd_linktree($scripturl.'?action=admin;area=tpblocks;sa=blocks', $txt['tp-blocks']);
	TPadd_linktree($scripturl.'?action=admin;area=tpblocks;sa=editblock;id='.$block_id . ';'.$context['session_var'].'='.$context['session_id'], $txt['tp-editblock']);

    $row = $tpBlock->getBlock($block_id);
    if(is_array($row)) {
		$acc2 = explode(',', $row['display']);
		$context['TPortal']['blockedit'] = $row;
		$context['TPortal']['blockedit']['var1']    = json_decode($row['settings'],true)['var1'];
		$context['TPortal']['blockedit']['var2']    = json_decode($row['settings'],true)['var2'];
		$context['TPortal']['blockedit']['var3']    = json_decode($row['settings'],true)['var3'];
		$context['TPortal']['blockedit']['var4']    = json_decode($row['settings'],true)['var4'];
		$context['TPortal']['blockedit']['var5']    = json_decode($row['settings'],true)['var5'];
		$context['TPortal']['blockedit']['display2'] = $context['TPortal']['blockedit']['display'];
		$context['TPortal']['blockedit']['body'] = $row['body'];
		unset($context['TPortal']['blockedit']['display']);
		$context['TPortal']['blockedit']['display'] = array(
			'action' => array(),
			'board' => array(),
			'page' => array(),
			'cat' => array(),
			'lang' => array(),
			'custo' => array(),
		);

		foreach($acc2 as $ss => $svalue) {
			if(substr($svalue, 0,6) == 'board=')
				$context['TPortal']['blockedit']['display']['board'][]  = substr($svalue,6);
			elseif(substr($svalue, 0, 6) == 'tpage=')
				$context['TPortal']['blockedit']['display']['page'][]   = substr($svalue,6);
			elseif(substr($svalue, 0, 6) == 'tpcat=')
				$context['TPortal']['blockedit']['display']['cat'][]    = substr($svalue,6);
			elseif(substr($svalue, 0, 6) == 'tpmod=')
				$context['TPortal']['blockedit']['display']['tpmod'][]  = substr($svalue,6);
			elseif(substr($svalue, 0, 6) == 'tlang=')
				$context['TPortal']['blockedit']['display']['lang'][]   = substr($svalue,6);
			elseif(substr($svalue, 0, 6) == 'custo=')
				$context['TPortal']['blockedit']['display']['custo']    = substr($svalue,6);
            else
				$context['TPortal']['blockedit']['display']['action'][] = $svalue;
		}

		// Add in BBC editor before we call in template so the headers are there
		if($context['TPortal']['blockedit']['type'] == '5') {
			$context['TPortal']['editor_id'] = 'tp_block_body';
			TP_prebbcbox($context['TPortal']['editor_id'], strip_tags($context['TPortal']['blockedit']['body']));
		}
        elseif($row['type'] == 8) {
            call_integration_hook('integrate_tp_shoutbox', array(&$row));
        }        
        elseif($row['type'] == 20) {
            call_integration_hook('integrate_tp_blocks', array(&$row));
        }

		if($context['TPortal']['blockedit']['lang'] != '') {
			$context['TPortal']['blockedit']['langfiles'] = array();
			$lang = explode('|', $context['TPortal']['blockedit']['lang']);
			$num = count($lang);
			for($i = 0; $i < $num; $i = $i + 2)
			{
				$context['TPortal']['blockedit']['langfiles'][$lang[$i]] = $lang[$i+1];
			}
		}
		// collect all available PHP block snippets
		$context['TPortal']['blockcodes'] = TPcollectSnippets();

        // Get the category names
        $categories = TPCategory::getInstance()->getCategoryData(array('id', 'display_name'), array('item_type' => 'category'));
        if(is_array($categories)) {
            foreach($categories as $k => $v) {
                $context['TPortal']['catnames'][$v['id']] = $v['display_name'];
				$context['TPortal']['article_categories'][] = array( 'id' => $v['id'], 'name' => $v['display_name'] );
            }
        }

		get_grps();
		get_langfiles();
		get_boards();
		get_articles();
		$context['TPortal']['edit_categories'] = array();

		// get all themes for selection
		$context['TPthemes'] = array();
		$request = $db->query('', '
			SELECT th.value AS name, th.id_theme as id_theme, tb.value AS path
			FROM {db_prefix}themes AS th
			LEFT JOIN {db_prefix}themes AS tb ON th.id_theme = tb.id_theme
			WHERE th.variable = {string:thvar}
			AND tb.variable = {string:tbvar}
			AND th.id_member = {int:id_member}
			ORDER BY th.value ASC',
			array(
				'thvar' => 'name', 'tbvar' => 'images_url', 'id_member' => 0,
			)
		);

		if($db->num_rows($request) > 0) {
			while ($row = $db->fetch_assoc($request)) {
				$context['TPthemes'][] = array(
					'id' => $row['id_theme'],
					'path' => $row['path'],
					'name' => $row['name']
				);
			}
			$db->free_result($request);
		}
	}
	// if not throw an error
	else {
		fatal_error($txt['tp-blockfailure'], false);
	}

	$context['sub_template'] = 'editblock';


    loadtemplate('TPBlockLayout');

}}}

function saveBlock( $block_id = 0 ) {{{
	global $settings, $context, $scripturl, $txt;

    if(empty($block_id)) {
	    $block_id  = TPUtil::filter('id', 'get', 'int');
    }

    // save a block?
    if(!is_numeric($block_id)) {
        fatal_error($txt['tp-notablock'], false);
    }
    $request =  $db->query('', '
        SELECT editgroups FROM {db_prefix}tp_blocks
        WHERE id = {int:blockid} LIMIT 1',
        array('blockid' => $block_id)
    );

    if($db->num_rows($request) > 0) {
        $row = $db->fetch_assoc($request);
        // check permission
        if(allowedTo('tp_blocks') || get_perm($row['editgroups'])) {
            $ok = true;
        }
        else {
            fatal_error($txt['tp-blocknotallowed'], false);
        }
        $db->free_result($request);

        // loop through the values and save them
        foreach ($_POST as $what => $value) {
            if(substr($what, 0, 10) == 'blocktitle') {
                // make sure special charachters can't be done
                $value = strip_tags($value);
                $value = preg_replace('~&#\d+$~', '', $value);
                $val = substr($what,10);
                $db->query('', '
                        UPDATE {db_prefix}tp_blocks
                        SET title = {string:title}
                        WHERE id = {int:blockid}',
                        array('title' => $value, 'blockid' => $val)
                        );
            }
            elseif(substr($what, 0, 9) == 'blockbody' && substr($what, -4) != 'mode') {
                // If we came from WYSIWYG then turn it back into BBC regardless.
                if (!empty($_REQUEST[$what.'_mode']) && isset($_REQUEST[$what])) {
                    require_once(SUBSDIR . '/Editor.subs.php');
                    $_REQUEST[$what] = html_to_bbc($_REQUEST[$what]);
                    // We need to unhtml it now as it gets done shortly.
                    $_REQUEST[$what] = un_htmlspecialchars($_REQUEST[$what]);
                    // We need this for everything else.
                    $value = $_POST[$what] = $_REQUEST[$what];
                }

                $val = (int) substr($what, 9);

                $db->query('', '
                        UPDATE {db_prefix}tp_blocks
                        SET body = {string:body}
                        WHERE id = {int:blockid}',
                        array('body' => $value, 'blockid' => $val)
                        );
            }
            elseif(substr($what, 0, 10) == 'blockframe') {
                $val = substr($what, 10);
                $db->query('', '
                        UPDATE {db_prefix}tp_blocks
                        SET frame = {string:frame}
                        WHERE id = {int:blockid}',
                        array('frame' => $value, 'blockid' => $val)
                        );
            }
            elseif(substr($what, 0, 12) == 'blockvisible') {
                $val = substr($what, 12);
                $db->query('', '
                        UPDATE {db_prefix}tp_blocks
                        SET visible = {string:vis}
                        WHERE id = {int:blockid}',
                        array('vis' => $value, 'blockid' => $val)
                        );
            }
        }
        redirectexit('action=tportal;sa=editblock'.$whatID);
    }
    else {
        fatal_error($txt['tp-notablock'], false);
    }

}}}

?>
