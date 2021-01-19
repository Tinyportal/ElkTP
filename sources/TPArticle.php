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
use \TinyPortal\Model\Database as TPDatabase;
use \TinyPortal\Model\Mentions as TPMentions;
use \TinyPortal\Model\Util as TPUtil;

if (!defined('ELK')) {
    die('Hacking attempt...');
}

// TinyPortal module entrance
function TPArticle() {{{

	global $settings, $context, $txt;

	if(loadLanguage('TParticle') == false) {
		loadLanguage('TParticle', 'english');
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

function TPArticleActions(&$subActions) {{{

    $subActions = array_merge(
        array (
            'editarticle'       => array('TPArticle.php', 'articleEdit', array()),
            'tpattach'          => array('TPArticle.php', 'articleAttachment', array()),
            'submitarticle'     => array('TPArticle.php', 'articleNew', array()),
            'addarticle_html'   => array('TPArticle.php', 'articleNew', array()),
            'addarticle_bbc'    => array('TPArticle.php', 'articleNew', array()),
            'publish'           => array('TPArticle.php', 'articlePublish', array()),
            'savearticle'       => array('TPArticle.php', 'articleEdit', array()),
            'uploadimage'       => array('TPArticle.php', 'articleUploadImage', array()),
            'submitsuccess'     => array('TPArticle.php', 'articleSubmitSuccess', array()),
        ),
        $subActions
    );

}}}

function articleAttachment() {{{
	tpattach();
}}}

function articleEdit() {{{
	global $context;

    $db = TPDatabase::getInstance();
	checkSession('post');
	isAllowedTo(array('tp_articles', 'tp_editownarticle', 'tp_submitbbc', 'tp_submithtml'));

	$options        = array();
	$article_data   = array();
    if(allowedTo('tp_alwaysapproved'))
		$article_data['approved'] = 1; // No approval needed
	else
		$article_data['approved'] = 0; // Preset to false

	foreach($_POST as $what => $value) {
		if(substr($what, 0, 11) == 'tp_article_') {
			$setting = substr($what, 11);
			if(substr($setting, 0, 8) == 'options_') {
				if(substr($setting, 0, 19) == 'options_lblockwidth' || substr($setting,0,19) == 'options_rblockwidth') {
					$options[] = substr($setting, 8).$value;
				}
				else {
					$options[] = substr($setting, 8);
				}
			}
			else {
				switch($setting) {
					case 'body_mode':
					case 'intro_mode':
					case 'illupload':
					case 'intro_pure':
					case 'body_pure':
					case 'body_choice':
						// We ignore all these
						break;
                    case 'title':
						$article_data['subject'] = $value;
                        break;
					case 'authorid':
						$article_data['author_id'] = $value;
						break;
					case 'idtheme':
						$article_data['id_theme'] = $value;
						break;
					case 'category':
						// for the event, get the allowed
						$request = $db->query('', '
							SELECT access FROM {db_prefix}tp_categories
							WHERE id = {int:varid} LIMIT 1',
							array('varid' => is_numeric($value) ? $value : 0 )
						);
						if($db->num_rows($request) > 0) {
							$row = $db->fetch_assoc($request);
							$allowed = $row['access'];
							$db->free_result($request);
						    $article_data['category'] = $value;
						}
						break;
					case 'shortname':
						$article_data[$setting] = htmlspecialchars(str_replace(' ', '-', $value), ENT_QUOTES);
						break;
					case 'intro':
					case 'body':
						// If we came from WYSIWYG then turn it back into BBC regardless.
						if (!empty($_REQUEST['tp_article_body_mode']) && isset($_REQUEST['tp_article_body'])) {
	                        require_once(SUBSDIR . '/Editor.subs.php');
							$_REQUEST['tp_article_body'] = html_to_bbc($_REQUEST['tp_article_body']);
							// We need to unhtml it now as it gets done shortly.
							$_REQUEST['tp_article_body'] = un_htmlspecialchars($_REQUEST['tp_article_body']);
							// We need this for everything else.
							if($setting == 'body') {
								$value = $_POST['tp_article_body'] = $_REQUEST['tp_article_body'];
							}
							elseif ($settings == 'intro') {
								$value = $_POST['tp_article_intro'] = $_REQUEST['tp_article_intro'];
							}
						}
						// in case of HTML article we need to check it
						if(isset($_POST['tp_article_body_pure']) && isset($_POST['tp_article_body_choice'])) {
							if($_POST['tp_article_body_choice'] == 0) {
								if ($setting == 'body') {
									$value = $_POST['tp_article_body_pure'];
								}
								elseif ($setting == 'intro') {
									$value = $_POST['tp_article_intro'];
								}
							}
						}
						$article_data[$setting] = $value;
						break;
					case 'day':
					case 'month':
					case 'year':
					case 'minute':
					case 'hour':
						$timestamp = mktime($_POST['tp_article_hour'], $_POST['tp_article_minute'], 0, $_POST['tp_article_month'], $_POST['tp_article_day'], $_POST['tp_article_year']);
						if(!isset($savedtime)) {
							$article_data['date'] = $timestamp;
						}
						break;
					case 'timestamp':
						if(!isset($savedtime)) {
						    $article_data['date'] = empty($_POST['tp_article_timestamp']) ? time() : $_POST['tp_article_timestamp'];
                        }
                        break;
					case 'pubstartday':
					case 'pubstartmonth':
					case 'pubstartyear':
					case 'pubstartminute':
					case 'pubstarthour':
					case 'pub_start':
						if(empty($_POST['tp_article_pubstarthour']) && empty($_POST['tp_article_pubstartminute']) && empty($_POST['tp_article_pubstartmonth']) && empty($_POST['tp_article_pubstartday']) && empty($_POST['tp_article_pubstartyear'])) {
							$article_data['pub_start'] = 0;
						}
						else {
							$timestamp = mktime($_POST['tp_article_pubstarthour'], $_POST['tp_article_pubstartminute'], 0, $_POST['tp_article_pubstartmonth'], $_POST['tp_article_pubstartday'], $_POST['tp_article_pubstartyear']);
							$article_data['pub_start'] = $timestamp;
						}
					break;
					case 'pubendday':
					case 'pubendmonth':
					case 'pubendyear':
					case 'pubendminute':
					case 'pubendhour':
					case 'pub_end':
						if(empty($_POST['tp_article_pubendhour']) && empty($_POST['tp_article_pubendminute']) && empty($_POST['tp_article_pubendmonth']) && empty($_POST['tp_article_pubendday']) && empty($_POST['tp_article_pubendyear'])) {
							$article_data['pub_end'] = 0;
						}
						else {
							$timestamp = mktime($_POST['tp_article_pubendhour'], $_POST['tp_article_pubendminute'], 0, $_POST['tp_article_pubendmonth'], $_POST['tp_article_pubendday'], $_POST['tp_article_pubendyear']);
							$article_data['pub_end'] = $timestamp;
						}
						break;
					default:
						$article_data[$setting] = $value;
						break;
				}
			}
		}
	}
	$article_data['options'] = implode(',', $options);
	// check if uploads are there
	if(array_key_exists('tp_article_illupload', $_FILES) && file_exists($_FILES['tp_article_illupload']['tmp_name'])) {
		$name = TPuploadpicture('tp_article_illupload', '', $context['TPortal']['icon_max_size'], 'jpg,gif,png', 'tp-files/tp-articles/illustrations');
		tp_createthumb('tp-files/tp-articles/illustrations/'. $name, $context['TPortal']['icon_width'], $context['TPortal']['icon_height'], 'tp-files/tp-articles/illustrations/s_'. $name);
		$article_data['illustration'] = $name;
	}

	$where      = TPUtil::filter('article', 'request', 'string');
	$tpArticle  = TPArticle::getInstance();
	if(empty($where)) {
		// We are inserting
		$where = $tpArticle->insertArticle($article_data);
	}
	else {
		// We are updating
		$tpArticle->updateArticle((int)$where, $article_data);
	}

	unset($tpArticle);
	// check if uploadad picture
	if(isset($_FILES['qup_tp_article_body']) && file_exists($_FILES['qup_tp_article_body']['tmp_name'])) {
        $name = TPuploadpicture( 'qup_tp_article_body', $context['user']['id'].'uid', null, null, $context['TPortal']['image_upload_path']);
		tp_createthumb($context['TPortal']['image_upload_path'].'/'. $name, 50, 50, $context['TPortal']['image_upload_path'].'/thumbs/thumb_'. $name);
	}
	// if this was a new article
	if(array_key_exists('tp_article_approved', $_POST) && $_POST['tp_article_approved'] == 1 && $_POST['tp_article_off'] == 0) {
		tp_recordevent($timestamp, $_POST['tp_article_authorid'], 'tp-createdarticle', 'page=' . $where, 'Creation of new article.', (isset($allowed) ? $allowed : 0) , $where);
	}

    if(array_key_exists('tpadmin_form', $_POST)) {
	    return $_POST['tpadmin_form'].';article='.$where;
    }
    else {
        redirectexit('action=tportal;sa=submitsuccess');
    }

}}}

function articleNew() {{{
    global $context, $settings;

    $db = TPDatabase::getInstance();

	require_once(SUBSDIR . '/TPortal.subs.php');

    // a BBC article?
    if(isset($_GET['bbc']) || $_GET['sa'] == 'addarticle_bbc') {
        isAllowedTo('tp_submitbbc');
        $context['TPortal']['articletype'] = 'bbc';
        $context['html_headers'] .= '
            <script type="text/javascript" src="'. $settings['default_theme_url']. '/scripts/editor.js?'.TPVERSION.'"></script>';

        // Add in BBC editor before we call in template so the headers are there
        $context['TPortal']['editor_id'] = 'tp_article_body';
        TP_prebbcbox($context['TPortal']['editor_id']);
    }
    else if($_GET['sa'] == 'addarticle_html') {
        $context['TPortal']['articletype'] = 'html';
        isAllowedTo('tp_submithtml');
        TPwysiwyg_setup();
    }
    else {
        redirectexit('action=forum');
    }

    $context['TPortal']['subaction'] = 'submitarticle';
	if(loadLanguage('TParticle') == false) {
		loadLanguage('TParticle', 'english');
    }
	if(loadLanguage('TPortalAdmin') == false) {
		loadLanguage('TPortalAdmin', 'english');
    }
    loadTemplate('TParticle');
    $context['sub_template'] = 'submitarticle';

}}}

function articleSubmitSuccess() {{{
    global $context;

    $context['TPortal']['subaction'] = 'submitsuccess';
    loadTemplate('TParticle');
	if(loadLanguage('TParticle') == false) {
		loadLanguage('TParticle', 'english');
    }
    $context['sub_template'] = 'submitsuccess';

}}}

function articlePublish() {{{
    global $context;

    // promoting topics
    if(!isset($_GET['t']))
        redirectexit('action=forum');

    $t = is_numeric($_GET['t']) ? $_GET['t'] : 0;

    if(empty($t))
        redirectexit('action=forum');

    isAllowedTo('tp_settings');
    $existing = explode(',', $context['TPortal']['frontpage_topics']);
    if(in_array($t, $existing))
        unset($existing[array_search($t, $existing)]);
    else
        $existing[] = $t;

    $newstring = implode(',', $existing);
    if(substr($newstring, 0, 1) == ',')
        $newstring = substr($newstring, 1);

    updateTPSettings(array('frontpage_topics' => $newstring));

    redirectexit('topic='. $t . '.0');

}}}

function articleUploadImage() {{{
    global $context, $boardurl;

	require_once(SUBSDIR . '/TPortal.subs.php');

    $name = TPuploadpicture( 'image', $context['user']['id'].'uid', null, null, $context['TPortal']['image_upload_path']);
    tp_createthumb( $context['TPortal']['image_upload_path'] . $name, 50, 50, $context['TPortal']['image_upload_path'].'thumbs/thumb_'.$name );
    $response['data'] = str_replace(BOARDDIR, $boardurl, $context['TPortal']['image_upload_path']) . $name;
    $response['success'] = 'true';
    header( 'Content-type: application/json' );
    echo json_encode( $response );
    // we want to just exit
    die;

}}}

function articleAjax() {{{
    global $context, $boardurl;

    $db         = TPDatabase::getInstance();
    $tpArticle  = TPArticle::getInstance();

	// first check any ajax stuff
	if(isset($_GET['arton'])) {
		checksession('get');
		$id     = is_numeric($_GET['arton']) ? $_GET['arton'] : '0';
        $col    = 'off';
        $tpArticle->toggleColumnArticle($id, $col);
    }
	elseif(isset($_GET['artlock'])) {
		checksession('get');
		$id     = is_numeric($_GET['artlock']) ? $_GET['artlock'] : '0';
        $col    = 'locked';
        $tpArticle->toggleColumnArticle($id, $col);
    }
	elseif(isset($_GET['artsticky'])) {
		checksession('get');
		$id     = is_numeric($_GET['artsticky']) ? $_GET['artsticky'] : '0';
        $col    = 'sticky';
        $tpArticle->toggleColumnArticle($id, $col);
	}
	elseif(isset($_GET['artfront'])) {
		checksession('get');
		$id     = is_numeric($_GET['artfront']) ? $_GET['artfront'] : '0';
        $col    = 'frontpage';
        $tpArticle->toggleColumnArticle($id, $col);
	}
	elseif(isset($_GET['artfeat'])) {
		checksession('get');
		$id     = is_numeric($_GET['artfeat']) ? $_GET['artfeat'] : '0';
        $col    = 'featured';
        $tpArticle->toggleColumnArticle($id, $col);
	}
	elseif(isset($_GET['catdelete'])) {
		checksession('get');
		$what = is_numeric($_GET['catdelete']) ? $_GET['catdelete'] : '0';
		if($what > 0) {
			// first get info
			$request = $db->query('', '
				SELECT id, parent FROM {db_prefix}tp_categories
				WHERE id = {int:varid} LIMIT 1',
				array('varid' => $what)
			);
			$row = $db->fetch_assoc($request);
			$db->free_result($request);

			$newcat = !empty($row['parent']) ? $row['parent'] : 0;

			$db->query('', '
				UPDATE {db_prefix}tp_categories
				SET parent = {string:val2}
				WHERE parent = {string:varid}',
				array(
					'val2' => $newcat, 'varid' => $what
				)
			);

			$db->query('', '
				DELETE FROM {db_prefix}tp_categories
				WHERE id = {int:varid}',
				array('varid' => $what)
			);
			$db->query('', '
				UPDATE {db_prefix}tp_articles
				SET category = {int:cat}
				WHERE category = {int:catid}',
				array('cat' => $newcat, 'catid' => $what)
			);
			redirectexit('action=admin;area=tparticles;sa=categories');
		}
		else {
			redirectexit('action=admin;area=tparticles;sa=categories');
		}
	}
	elseif(isset($_GET['artdelete'])) {
		checksession('get');
		$what = is_numeric($_GET['artdelete']) ? $_GET['artdelete'] : '0';
		$cu = is_numeric($_GET['cu']) ? $_GET['cu'] : '';
		if($cu == -1) {
			$strays=true;
			$cu = '';
		}
		if($what > 0) {
			$db->query('', '
				DELETE FROM {db_prefix}tp_articles
				WHERE id = {int:artid}',
				array('artid' => $what)
			);
			$db->query('', '
				DELETE FROM {db_prefix}tp_categories
				WHERE page = {int:artid}',
				array('artid' => $what)
			);
		}
		redirectexit('action=admin;area=tparticles' . (!empty($cu) ? ';cu='.$cu : '') . (isset($strays) ? ';sa=strays'.$cu : ';sa=articles'));
	}

    unset($tpArticle);

}}}

function TPArticleAdminActions(&$subActions) {{{

    return;

    $subActions = array_merge(
        array (
            'killcomment'       => array('TPArticle.php', 'articleDeleteComment', array()),
            'editcomment'       => array('TPArticle.php', 'articleEditComment', array()),
            'editarticle'       => array('TPArticle.php', 'articleEdit', array()),
            'tpattach'          => array('TPArticle.php', 'articleAttachment', array()),
            'submitarticle'     => array('TPArticle.php', 'articleNew', array()),
            'addarticle_html'   => array('TPArticle.php', 'articleNew', array()),
            'addarticle_bbc'    => array('TPArticle.php', 'articleNew', array()),
            'publish'           => array('TPArticle.php', 'articlePublish', array()),
            'savearticle'       => array('TPArticle.php', 'articleEdit', array()),
            'uploadimage'       => array('TPArticle.php', 'articleUploadImage', array()),
            'submitsuccess'     => array('TPArticle.php', 'articleSubmitSuccess', array()),
        ),
        $subActions
    );

}}}

?>
