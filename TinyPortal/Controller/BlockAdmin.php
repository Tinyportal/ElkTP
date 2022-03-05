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
namespace TinyPortal\Controller;

use \TinyPortal\Model\Admin as TPAdmin;
use \TinyPortal\Model\Article as TPArticle;
use \TinyPortal\Model\Block as TPBlock;
use \TinyPortal\Model\Category as TPCategory;
use \TinyPortal\Model\Database as TPDatabase;
use \TinyPortal\Model\Integrate as TPIntegrate;
use \TinyPortal\Model\Mentions as TPMentions;
use \TinyPortal\Model\Permissions as TPPermissions;
use \TinyPortal\Model\Subs as TPSubs;
use \TinyPortal\Model\Util as TPUtil;
use \ElkArte\Errors\Errors;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class BlockAdmin extends \Action_Controller
{

    // Admin Actions
    public function action_index() {{{
        global $context, $txt;

        $area = TPUtil::filter('area', 'get', 'string');

        if($area == 'tpblocks') {
            require_once(SUBSDIR . '/Action.class.php');

            $sa = TPUtil::filter('sa', 'get', 'string');
            if($sa == false) {
                $sa = 'blocks';
            }

            $subActions = array (
                'blocks'        => array($this, 'action_block', array()),
                'addblock'      => array($this, 'action_block', array()),
                'editblock'     => array($this, 'action_edit', array()),
                'updateblock'   => array($this, 'action_update', array()),
                'updateblocks'  => array($this, 'action_updates', array()),
                'saveblock'     => array($this, 'action_save', array()),
                'blockon'       => array($this, 'action_ajax', array()),
                'blockoff'      => array($this, 'action_ajax', array()),
                'blockdelete'   => array($this, 'action_ajax', array()),
                'blockleft'     => array($this, 'action_ajax', array()),
                'blocktop'      => array($this, 'action_ajax', array()),
                'blockcenter'   => array($this, 'action_ajax', array()),
                'blockfront'    => array($this, 'action_ajax', array()),
                'blocklower'    => array($this, 'action_ajax', array()),
                'blockbottom'   => array($this, 'action_ajax', array()),
                'blockright'    => array($this, 'action_ajax', array()),
                'addpos'        => array($this, 'action_ajax', array()),
                'subpos'        => array($this, 'action_ajax', array()),
                'blktype'       => array($this, 'action_ajax', array()),
                'panels'        => array($this, 'action_panels', array()),
                'updatepanels'  => array($this, 'action_panels', array()),
                'blockoverview' => array($this, 'action_show', array()),
                'updateoverview'=> array($this, 'action_overview', array()),
            );

            if(TPSubs::getInstance()->loadLanguage('TPortalAdmin') == false) {
                TPSubs::getInstance()->loadLanguage('TPortalAdmin', 'english');
            }
            if(TPSubs::getInstance()->loadLanguage('TPortal') == false) {
                TPSubs::getInstance()->loadLanguage('TPortal', 'english');
            }
            if(TPSubs::getInstance()->loadLanguage('TPmodules') == false) {
                TPSubs::getInstance()->loadLanguage('TPmodules', 'english');
            }

            $context['TPortal']['subaction'] = $sa;

            $action     = new \Action();
            $subAction  = $action->initialize($subActions, $sa);
            $action->dispatch($subAction);

            TPAdmin::getInstance()->topMenu($sa);
            TPAdmin::getInstance()->sideMenu($sa);

            $context['sub_template']         = $context['TPortal']['subaction'];

            \loadTemplate('TPBlockAdmin');
            \loadTemplate('TPsubs');

        }

    }}}

    public function action_block() {{{
        global $context, $txt, $settings, $scripturl;

        \isAllowedTo('tp_blocks');

        $tpBlock    = TPBlock::getInstance();

        if(($context['TPortal']['subaction'] == 'blocks')) {
            TPSubs::getInstance()->addLinkTree($scripturl.'?action=admin;area=tpblocks;sa=blocks', $txt['tp-blocks']);
        }
        else if($context['TPortal']['subaction'] == 'addblock') {
            TPSubs::getInstance()->addLinkTree($scripturl.'?action=admin;area=tpblocks;sa=addblock', $txt['tp-addblock']);
            // collect all available PHP block snippets
            $context['TPortal']['blockcodes']   = TPSubs::getInstance()->collectSnippets();
            $context['TPortal']['copyblocks']   = $tpBlock->getBlocks();
            $context['TPortal']['blockside']    = TPUtil::filter('side', 'get', 'string');
            TPSubs::getInstance()->articles();
            // check which side its mean to be on
        }

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

        TPSubs::getInstance()->articles();

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
                Ajax.open("POST", "?action=admin;area=tpblocks;sa=blockon;id=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'");
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
                var params = "?action=admin;area=tpblocks;sa=blockon;id=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'";
                Ajax.send(params);
            }
        // ]]></script>';

    }}}

    public function action_edit( $block_id = 0 ) {{{

        global $settings, $context, $scripturl, $txt;

        $tpBlock    = TPBlock::getInstance();
        $db         = TPDatabase::getInstance();

        if(empty($block_id)) {
            $block_id  = TPUtil::filter('id', 'get', 'int');
        }

        if(!is_numeric($block_id)) {
            throw new \Elk_Exception($txt['tp-notablock'], 'general');
        }

        if(TPSubs::getInstance()->loadLanguage('TPortalAdmin') == false) {
            TPSubs::getInstance()->loadLanguage('TPortalAdmin', 'english');
        }

        checksession('get');

        TPSubs::getInstance()->addLinkTree($scripturl.'?action=admin;area=tpblocks;sa=blocks', $txt['tp-blocks']);
        TPSubs::getInstance()->addLinkTree($scripturl.'?action=admin;area=tpblocks;sa=editblock;id='.$block_id . ';'.$context['session_var'].'='.$context['session_id'], $txt['tp-editblock']);

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
                TPSubs::getInstance()->prebbcbox($context['TPortal']['editor_id'], strip_tags($context['TPortal']['blockedit']['body']));
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
            $context['TPortal']['blockcodes'] = TPSubs::getInstance()->collectSnippets();

            // Get the category names
            $categories = TPCategory::getInstance()->select(array('id', 'display_name'), array('item_type' => 'category'));
            if(is_array($categories)) {
                foreach($categories as $k => $v) {
                    $context['TPortal']['catnames'][$v['id']] = $v['display_name'];
                    $context['TPortal']['article_categories'][] = array( 'id' => $v['id'], 'name' => $v['display_name'] );
                }
            }

            TPSubs::getInstance()->grps();
            TPSubs::getInstance()->langfiles();
            TPSubs::getInstance()->boards();
            TPSubs::getInstance()->articles();
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
            throw new \Elk_Exception($txt['tp-blockfailure'], 'general');
        }

        $context['sub_template'] = 'editblock';

        \loadTemplate('TPBlockLayout');

    }}}

    public function action_updates() {{{
        global $context;

        $blocks = array();

        if(is_array($_POST) && count($_POST)) {
            foreach($_POST as $k => $v) {
                foreach(array('pos', 'title', 'type', 'blockbody') as $type) {
                    if(strstr($k, $type)) {
                        $id = str_replace($type, '', $k);
                        if($type == 'blockbody') {
                            $type = 'body';
                        }
                        $blocks[$id][$type] = $v;
                    }
                }
            }
        }

        foreach($blocks as $block_id => $updateArray) {
            TPBlock::getInstance()->update($block_id, $updateArray);
        }

        $context['TPortal']['subaction'] = 'blocks';
        self::action_block();

    }}}

    public function action_update( $block_id = 0 ) {{{
        global $settings, $context, $scripturl, $txt;

        \checkSession('post');
        \isAllowedTo('tp_blocks');

        $tpBlock = TPBlock::getInstance();

        if(empty($block_id)) {
            $block_id  = TPUtil::filter('id', 'get', 'int');
        }

        // save a block?
        if(!is_numeric($block_id)) {
            throw new \Elk_Exception($txt['tp-notablock'], 'general');
        }

		$access 	= array();
		$tpgroups 	= array();
		$editgroups = array();
		$lang 		= array();

        foreach($_POST as $k => $v) {
            // We have a empty post value just skip it
            if(empty($v) && $v == '') {
                continue;
            }

            if(substr($k, 0, 9) == 'tp_block_') {
                $setting = substr($k, 9);
                switch($setting) {
                    case 'body':
                        if(TPUtil::filter('tp_block_body_mode', 'request', 'string')) {
                            if($body    = TPUtil::filter('tp_block_body', 'request', 'string')) {
                                require_once(SOURCEDIR . '/Editor.subs.php');
                                $body   = \html_to_bbc($body);
                                $body   = \un_htmlspecialchars($body);
                                $updateArray[$setting] = $body;
                            }
                        }
                        else if(TPUtil::filter('tp_block_mode', 'post', 'string') == 10) {
                            $updateArray[$setting] = tp_convertphp($v);
                        }
                        else {
                            $updateArray[$setting] = $v;
                        }
                        break;
                    case 'body_mode':
                    case 'body_pure':
                    case 'body_choice':
                        // Do nothing
                        break;
                    case 'var1':
                    case 'var2':
                    case 'var3':
                    case 'var4':
                    case 'var5':
                        $existing = $tpBlock->select(array('settings'), array('id' => $block_id));
                        if(is_array($existing)) {
                            $data   = json_decode($existing[0]['settings'], true);
                        }
                        $data[$setting] = $v;
                        $updateArray['settings'] = json_encode($data);
                        break;
                    default:
                        $updateArray[$setting] = $v;
                        break;
                }
            }
			elseif(substr($k, 0, 8) == 'tp_group') {
					$tpgroups[] = substr($k, 8);
			}
			elseif(substr($k, 0, 12) == 'tp_editgroup') {
				$editgroups[] = substr($k, 12);
			}
			elseif(substr($k, 0, 10) == 'actiontype') {
				$access[] = '' . $v;
			}
			elseif(substr($k, 0, 9) == 'boardtype') {
				$access[] = 'board=' . $v;
			}
			elseif(substr($k, 0, 11) == 'articletype') {
				$access[] = 'tpage=' . $v;
			}
			elseif(substr($k, 0, 12) == 'categorytype') {
				$access[] = 'tpcat=' . $v;
			}
			elseif(substr($k, 0, 8) == 'langtype') {
				$access[] = 'tlang=' . $k;
			}
			elseif(substr($k, 0, 9) == 'custotype' && !empty($v)) {
				$items = explode(',', $v);
				foreach($items as $iti => $it)
					$access[] = '' . $it;
			}
			elseif(substr($k, 0, 8) == 'tp_lang_') {
				if(substr($k, 8) != '' )
					$lang[] = substr($k, 8). '|' . $v;
			}
			elseif(substr($k, 0, 18) == 'tp_userbox_options') {
				if(!isset($userbox)) {
					$userbox = array();
				}
				$userbox[] = $value;
			}
			elseif(substr($k, 0, 8) == 'tp_theme') {
				$theme = substr($k, 8);
				if(!isset($themebox)) {
					$themebox = array();
				}
				// get the path too
				if(isset($_POST['tp_path'.$theme]))
					$tpath = $_POST['tp_path'.$theme];
				else
					$tpath = '';

				$themebox[] = $theme . '|' . $v . '|' . $tpath;
			}
			elseif(substr($k, 0, 12) == 'tp_blockcode') {
                if(!empty($_POST['tp_blockcode'])) {
                    $updateArray['body'] = TPSubs::getInstance()->parseModfile(file_get_contents($context['TPortal']['blockcode_upload_path'] . $_POST['tp_blockcode'].'.blockcode') , array('code'))['code'];
                }
            }

			$updateArray['display'] 	= implode(',', $access);
			$updateArray['access'] 		= implode(',', $tpgroups);
			$updateArray['lang'] 		= implode('|', $lang);
			$updateArray['editgroups'] 	= implode('|', $editgroups);
        }

		if(isset($userbox)) {
			//$updateArray['userbox_options'] = implode(',', $userbox);
		}

		if(isset($themebox)) {
			$updateArray['body'] = implode(',', $themebox);
		}

        $tpBlock->update($block_id, $updateArray);

        redirectexit('action=admin;area=tpblocks;sa=editblock&id='.$block_id.';' . $context['session_var'] . '=' . $context['session_id']);

    }}}

    public function action_save( ) {{{
        global $settings, $context, $scripturl, $txt;

        checkSession('post');
        isAllowedTo('tp_blocks');

        $title  = TPUtil::filter('tp_addblocktitle', 'post', 'string');
        if($title == false) {
            $title = $txt['tp-no_title'];
        }

        $panel  = TPUtil::filter('tp_addblockpanel', 'post', 'string');
        $type   = TPUtil::filter('tp_addblock', 'post', 'string');
        if(!is_numeric($type)) {
            if(substr($type, 0, 3) == 'mb_') {
                $cp = TPBlock::getInstance()->select(array('*'), array('id' => substr($type, 3)));
                if(is_array($cp)) {
                    $cp = $cp[0];
                }
            }
            else {
                $od     = TPSubs::getInstance()->parseModfile(file_get_contents($context['TPortal']['blockcode_upload_path'] . $type.'.blockcode') , array('code'));
                $body   = TPSubs::getInstance()->convertphp($od['code']);
				$type   = 10;
            }
        }

        $body = '';
        if(in_array($type , array('18', '19'))) {
            $body = '0';
        }

        // Find the last position
        $position   = 0;
        $pos        = TPBlock::getInstance()->select(array('pos'), array('bar' => $panel));
        if(is_array($pos)) {
            foreach($pos as $k => $v) {
                if($position <= $v['pos']) {
                    $position = $v['pos'] + 1;
                }
            }
        }
        else {
            $position = 0;
        }

        if(isset($cp)) {
            $block = array ( 'type' => $cp['type'], 'frame' => $cp['frame'], 'title' => $title, 'body' => $cp['body'], 'access' => $cp['access'], 'bar' => $panel, 'pos' => $position, 'off' => 1, 'visible' => 1, 'lang' => $cp['lang'], 'display' => $cp['display'], 'editgroups' => $cp['editgroups'], 'settings' => json_encode(array(
                'var1' => json_decode($cp['settings'], true)['var1'],
                'var2' => json_decode($cp['settings'], true)['var2'],
                'var3' => 0,
                'var4' => 0,
                'var5' => 0)
            ));
        }
        else {
            $block = array ( 'type' => $type, 'frame' => 'frame', 'title' => $title, 'body' => $body, 'access' => '-1,0,1', 'bar' => $panel, 'pos' => $position, 'off' => 1, 'visible' => 1, 'lang' => '', 'display' => 'allpages', 'editgroups' => '', 'settings' => json_encode(array('var1' => 0, 'var2' => 0, 'var3' => 0, 'var4' => 0, 'var5' => 0 )));
        }

        $id = TPBlock::getInstance()->insert($block);
        if(!empty($id)) {
		    redirectexit('action=admin;area=tpblocks;sa=editblock&id='.$id.';sesc='. $context['session_id']);
        }
		else {
			redirectexit('action=admin;area=tpblocks;sa=blocks');
        }

    }}}

    public function action_ajax() {{{

        checksession('get');
        isAllowedTo('tp_blocks');

        $id = TPUtil::filter('id', 'get', 'int');
        if($id != false) {
            $tpBlock    = TPBlock::getInstance();
            $subAction  = TPUtil::filter('sa', 'get', 'string');
            switch($subAction) {
                case 'blockdelete':
                    $tpBlock->delete($id);
                    redirectexit('action=admin;area=tpblocks;sa=blocks');
                    break;
                case 'blockright':
                case 'blockleft':
                case 'blockcenter':
                case 'blockfront':
                case 'blockbottom':
                case 'blocktop':
                case 'blocklower':
                    $loc    = $tpBlock->getBlockBarId(str_replace('block', '', $subAction));
                    $tpBlock->update($id, array( 'bar' => $loc ));
                    redirectexit('action=admin;area=tpblocks;sa=blocks');
                    break;
                case 'addpos':
                    $current    = $tpBlock->select(array( 'pos', 'bar'), array( 'id' => $id) );
                    $new        = $current[0]['pos'] + 1;
                    $existing   = $tpBlock->select('id', array( 'bar' => $current[0]['bar'], 'pos' => $new ) );
                    if(is_array($existing)) {
                        $tpBlock->update($existing[0]['id'], array( 'pos' => $current[0]['pos']));
                    }
                    $tpBlock->update($id, array( 'pos' => $new));
                    \redirectexit('action=admin;area=tpblocks;sa=blocks');
                    break;
                case 'subpos':
                    $current    = $tpBlock->select(array( 'pos', 'bar'), array( 'id' => $id) );
                    $new        = $current[0]['pos'] - 1;
                    $existing   = $tpBlock->select('id', array( 'bar' => $current[0]['bar'], 'pos' => $new ) );
                    if(is_array($existing)) {
                        $tpBlock->update($existing[0]['id'], array( 'pos' => $current[0]['pos']));
                    }
                    $tpBlock->update($id, array( 'pos' => $new));
                    \redirectexit('action=admin;area=tpblocks;sa=blocks');
                    break;
                case 'blockon':
                    $current    = $tpBlock->select(array( 'off' ), array( 'id' => $id) );
                    if(is_array($current)) {
                        if($current[0]['off'] == 1) {
                            $tpBlock->update($id, array( 'off' => '0' ));
                        }
                        else {
                            $tpBlock->update($id, array( 'off' => '1' ));
                        }
                    }
                    break;
                default:
                    break;
            }
        }

    }}}

    public function action_show() {{{
        global $context, $txt, $scripturl;

        // are we on overview screen?
        if($context['TPortal']['subaction'] == 'blockoverview') {
            TPSubs::getInstance()->addLinkTree($scripturl.'?action=admin;area=tpblocks;sa=blockoverview', $txt['tp-blockoverview']);

            // fetch all blocks member group permissions
            $data   = TPBlock::getInstance()->select(array('id', 'title', 'bar', 'access', 'type'), array( 'off' => 0 ) );
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
            TPSubs::getInstance()->grps(true,true);
        }

    }}}

    public function action_overview() {{{

		checkSession('post');
		isAllowedTo('tp_blocks');

		$tpBlock	= TPBlock::getInstance();
		$block 		= array();

		foreach($_POST as $what => $value) {
			if(substr($what, 5, 7) == 'tpblock') {
				// get the id
				$bid = substr($what, 12);
				if(!isset($block[$bid])) {
					$block[$bid] = array();
				}

				if($value != 'control' && !in_array($value, $block[$bid])) {
					$block[$bid][] = $value;
				}
			}
		}

		foreach($block as $bl => $blo) {
			if($tpBlock->select('access', array('id' => $bl))) {
				$tpBlock->update($bl, array('access' => implode(',', $blo)));
			}
		}

        redirectexit('action=admin;area=tpblocks;sa=blockoverview;' . $context['session_var'] . '=' . $context['session_id']);

    }}}

    public function action_panels() {{{
        global $txt, $context;

        if($context['TPortal']['subaction'] == 'panels') {
            // We don't do anything for panels view
            return;
        }

        $updateArray = array();

        $checkboxes = array('hidebars_admin_only', 'hidebars_profile', 'hidebars_pm', 'hidebars_memberlist', 'hidebars_search', 'hidebars_calendar');
        foreach($checkboxes as $v) {
            if(TPUtil::checkboxChecked('tp_'.$v)) {
                $updateArray[$v] = 1;
            }
            else {
                $updateArray[$v] = 0;
            }
            // remove the variable so we don't process it twice before the old logic is removed
            unset($_POST['tp_'.$v]);
        }

        foreach($_POST as $what => $value) {
            if(substr($what, 0, 3) == 'tp_') {
                $where                  = substr($what, 3);
                $updateArray[$where]    = $value;
            }
        }

        TPSubs::getInstance()->updateSettings($updateArray);

        redirectexit('action=admin;area=tpblocks;sa=panels;' . $context['session_var'] . '=' . $context['session_id']);
    }}}

}
?>
