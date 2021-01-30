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
namespace TinyPortal\Controller;

use \TinyPortal\Model\Admin as TPAdmin;
use \TinyPortal\Model\Article as TPArticle;
use \TinyPortal\Model\Block as TPBlock;
use \TinyPortal\Model\Category as TPCategory;
use \TinyPortal\Model\Database as TPDatabase;
use \TinyPortal\Model\Permissions as TPPermissions;
use \TinyPortal\Model\Mentions as TPMentions;
use \TinyPortal\Model\Util as TPUtil;
use \ElkArte\Errors\Errors;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class ArticleAdmin extends \Action_Controller
{

    public function action_index() {{{

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

        require_once(SUBSDIR . '/Action.class.php');
        $subActions = array (
            'editarticle'       => array($this, 'articleEdit', array()),
            'tpattach'          => array($this, 'articleAttachment', array()),
            'submitarticle'     => array($this, 'articleNew', array()),
            'publish'           => array($this, 'articlePublish', array()),
            'savearticle'       => array($this, 'articleEdit', array()),
            'uploadimage'       => array($this, 'articleUploadImage', array()),
            'submitsuccess'     => array($this, 'articleSubmitSuccess', array()),
            'myarticles'        => array(new Article, 'articleShow', array()),
            // FIXME split these out into the correct functions rather than calling the old method
            'addarticle_bbc'    => array($this, 'TPortalAdmin', array()),
            'addarticle_html'   => array($this, 'TPortalAdmin', array()),
            'addarticle_php'    => array($this, 'TPortalAdmin', array()),
            'addarticle_import' => array($this, 'TPortalAdmin', array()),
            'articles'          => array($this, 'TPortalAdmin', array()),
            'strays'            => array($this, 'TPortalAdmin', array()),
            'submission'        => array($this, 'TPortalAdmin', array()),
            'categories'        => array($this, 'TPortalAdmin', array()),
            'addcategory'       => array($this, 'TPortalAdmin', array()),
            'clist'             => array($this, 'TPortalAdmin', array()),
            'artsettings'       => array($this, 'TPortalAdmin', array()),
            'articons'          => array($this, 'TPortalAdmin', array()),
        );

        $sa = TPUtil::filter('sa', 'get', 'string');

        $context['TPortal']['subaction'] = $sa;

        TPAdmin::getInstance()->topMenu($sa);
        TPAdmin::getInstance()->sideMenu($sa);

        $action     = new \Action();
        $subAction  = $action->initialize($subActions, $sa);
        $action->dispatch($subAction);

    }}}

    public function articleAttachment() {{{
        tpattach();
    }}}

    public function articleEdit() {{{
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

    public function articleNew() {{{
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

    public function articleSubmitSuccess() {{{
        global $context;

        $context['TPortal']['subaction'] = 'submitsuccess';
        loadTemplate('TParticle');
        if(loadLanguage('TParticle') == false) {
            loadLanguage('TParticle', 'english');
        }
        $context['sub_template'] = 'submitsuccess';

    }}}

    public function articlePublish() {{{
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

    public function articleUploadImage() {{{
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

    public function articleAjax() {{{
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

    public function TPortalAdmin() {{{
        global $scripturl, $context, $txt;

        if(loadLanguage('TPortalAdmin') == false)
            loadLanguage('TPortalAdmin', 'english');
        if(loadLanguage('TPortal') == false)
            loadLanguage('TPortal', 'english');

        require_once(SUBSDIR . '/Post.subs.php');
        require_once(SUBSDIR . '/TPortal.subs.php');

        // some GET values set up
        $context['TPortal']['tpstart'] = isset($_GET['tpstart']) ? $_GET['tpstart'] : 0;

        // a switch to make it clear what is "forum" and not
        $context['TPortal']['not_forum'] = true;

        // get all member groups
        tp_groups();

        // get the layout schemes
        get_catlayouts();

        // Get the category names
        $categories = TPCategory::getInstance()->getCategoryData(array('id', 'display_name'), array('item_type' => 'category'));
        if(is_array($categories)) {
            foreach($categories as $k => $v) {
                $context['TPortal']['catnames'][$v['id']] = $v['display_name'];
            }
        }

        if(isset($_GET['id'])) {
            $context['TPortal']['subaction_id'] = $_GET['id'];
        }

        // check POST values
        $return = $this->do_postchecks();

        if(!empty($return)) {
            redirectexit('action=admin;area=tparticles;sa=' . $return);
        }

        $tpsub = '';

        $subAction  = TPUtil::filter('sa', 'get', 'string');
        if($subAction == false) {
            $subAction  = TPUtil::filter('area', 'get', 'string');
        }
        $subActions = array();

        call_integration_hook('integrate_tp_pre_admin_subactions', array(&$subActions));

        $context['TPortal']['subaction'] = $subAction;

        // If it exists in our new subactions array load it
        if(!empty($subAction) && array_key_exists($subAction, $subActions)) {
            if (!empty($subActions[$subAction][0])) {
                require_once(SOURCEDIR . '/' . $subActions[$subAction][0]);
            }
            call_user_func_array($subActions[$subAction][1], $subActions[$subAction][2]);
        }
        elseif(isset($_GET['sa'])) {
            $context['TPortal']['subaction'] = $tpsub = $_GET['sa'];
            if(substr($_GET['sa'], 0, 11) == 'editarticle') {
                loadTemplate('TParticle');
                $context['sub_template'] = 'submitarticle';
                $tpsub = 'articles';
                $context['TPortal']['subaction'] = 'editarticle';
            }
            elseif(substr($_GET['sa'], 0, 11) == 'addarticle_') {
                loadTemplate('TParticle');
                $context['sub_template'] = 'submitarticle';
                $tpsub = 'articles';
                $context['TPortal']['subaction'] = $_GET['sa'];
                if($_GET['sa'] == 'addarticle_html') {
                    TPwysiwyg_setup();
                }
            }

            $this->do_subaction($tpsub);
        }
        elseif(isset($_GET['catdelete']) || isset($_GET['artfeat']) || isset($_GET['artfront']) || isset($_GET['artdelete']) || isset($_GET['arton']) || isset($_GET['artoff']) || isset($_GET['artsticky']) || isset($_GET['artlock']) || isset($_GET['catcollapse'])) {
            if(allowedTo('tp_articles')) {
                $context['TPortal']['subaction'] = $tpsub = 'articles';
                $this->do_articles($tpsub);
            }
            else {
                fatal_error($txt['tp-noadmin'], false);
            }
        }

        get_boards();
        $context['TPortal']['SSI_boards'] = explode(',', $context['TPortal']['SSI_board']);

        TPAdmin::getInstance()->topMenu($tpsub);
        TPAdmin::getInstance()->sideMenu($tpsub);

        \loadTemplate('TPortalAdmin');
        \loadTemplate('TPsubs');

        \validateSession();

        call_integration_hook('integrate_tp_post_admin_subactions');
    }}}

    public function do_subaction($tpsub) {{{
        global $context, $txt;

        if(in_array($tpsub, array('articles', 'strays', 'categories', 'addcategory', 'submission', 'artsettings', 'articons', 'clist')) && (allowedTo(array('tp_articles', 'tp_editownarticle'))) )  {
            $this->do_articles();
        }
        elseif(!$context['user']['is_admin']) {
            fatal_error($txt['tp-noadmin'], false);
        }
        else {
            redirectexit('action=admin;area=tpsettings');
        }

    }}}

    public function do_articles() {{{
        global $context, $txt, $settings, $boardurl, $scripturl;

        $db = TPDatabase::getInstance();

        if(allowedTo('tp_articles') == false) {
            if(isset($_GET['sa']) && substr($_GET['sa'], 0, 11) == 'editarticle') {
                $article = TPUtil::filter('article', 'get', 'string');
                $request = $db->query('', '
                    SELECT id FROM {db_prefix}tp_articles
                    WHERE id = {int:article_id}
                    AND author_id = {int:member_id}',
                    array(
                        'article_id'    => $article,
                        'member_id'     => $context['user']['id']
                    )
                );
                if($db->num_rows($request) == 0) {
                    fatal_error($txt['tp-noadmin'], false);
                }
                $db->free_result($request);
            }
            else {
                fatal_error($txt['tp-noadmin'], false);
            }
        }

        // do an update of stray articles and categories
        $acats = array();
        $request = $db->query('', '
            SELECT id FROM {db_prefix}tp_categories
            WHERE item_type = {string:type}',
            array('type' => 'category')
        );
        if($db->num_rows($request) > 0)
        {
            while($row = $db->fetch_assoc($request))
                $acats[] = $row['id'];
            $db->free_result($request);
        }
        if(count($acats) > 0)
        {
            $db->query('', '
                UPDATE {db_prefix}tp_categories
                SET parent = {int:val2}
                WHERE item_type = {string:type}
                AND parent NOT IN ({array_string:parent})',
                array('val2' => 0, 'type' => 'category', 'parent' => $acats)
            );
            $db->query('', '
                UPDATE {db_prefix}tp_articles
                SET category = {int:cat}
                WHERE category NOT IN({array_int:category})
                AND category > 0',
                array('cat' => 0, 'category' => $acats)
            );
        }

        $this->articleAjax();

        // for the non-category articles, do a count.
        $request = $db->query('', '
            SELECT COUNT(*) as total
            FROM {db_prefix}tp_articles
            WHERE category = 0 OR category = 9999'
        );

        $row = $db->fetch_assoc($request);
        $context['TPortal']['total_nocategory'] = $row['total'];
        $db->free_result($request);

        // for the submissions too
        $request = $db->query('', '
            SELECT COUNT(*) as total
            FROM {db_prefix}tp_articles
            WHERE approved = 0'
        );

        $row = $db->fetch_assoc($request);
        $context['TPortal']['total_submissions'] = $row['total'];
        $db->free_result($request);

        // we are on categories screen
        if(in_array($context['TPortal']['subaction'], array('categories', 'addcategory', 'clist'))) {
            TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa=categories', $txt['tp-categories']);
            // first check if we simply want to copy or set as child
            if(isset($_GET['cu']) && is_numeric($_GET['cu'])) {
                $ccat = $_GET['cu'];
                if(isset($_GET['copy'])) {
                    $request = $db->query('', '
                        SELECT * FROM {db_prefix}tp_categories
                        WHERE id = {int:varid}',
                        array('varid' => $ccat)
                    );
                    if($db->num_rows($request) > 0) {
                        $row = $db->fetch_assoc($request);
                        $row['display_name'] .= '__copy';
                        $db->free_result($request);
                        $db->insert('insert',
                            '{db_prefix}tp_categories',
                            array(
                                'display_name' => 'string',
                                'parent' => 'string',
                                'access' => 'string',
                                'item_type' => 'string',
                                'dt_log' => 'string',
                                'page' => 'int',
                                'settings' => 'string',
                                'short_name' => 'string',
                            ),
                            array(
                                $row['display_name'],
                                $row['parent'],
                                $row['access'],
                                $row['type'],
                                $row['dt_log'],
                                $row['page'],
                                $row['settings'],
                                $row['short_name'],
                            ),
                            array('id')
                        );
                    }
                    redirectexit('action=admin;area=tparticles;sa=categories');
                }
                elseif(isset($_GET['child'])) {
                    $request = $db->query('', '
                        SELECT * FROM {db_prefix}tp_categories
                        WHERE id = {int:varid}',
                        array('varid' => $ccat)
                    );
                    if($db->num_rows($request) > 0) {
                        $row = $db->fetch_assoc($request);
                        $row['display_name'] .= '__copy';
                        $db->free_result($request);
                        $db->insert('INSERT',
                            '{db_prefix}tp_categories',
                            array(
                                'display_name' => 'string',
                                'parent' => 'string',
                                'access' => 'string',
                                'item_type' => 'string',
                                'dt_log' => 'string',
                                'page' => 'int',
                                'settings' => 'string',
                                'short_name' => 'string',
                            ),
                            array(
                                $row['display_name'],
                                $row['id'],
                                $row['access'],
                                $row['type'],
                                $row['dt_log'],
                                $row['page'],
                                $row['settings'],
                                $row['short_name'],
                            ),
                            array('id')
                        );
                    }
                    redirectexit('action=admin;area=tparticles;sa=categories');
                }
                // guess we only want the category then
                else {
                    // get membergroups
                    get_grps();
                $context['html_headers'] .= '
                <script type="text/javascript"><!-- // --><![CDATA[
                    function changeIllu(node,name)
                    {
                        node.src = \'' . $boardurl . '/tp-files/tp-articles/illustrations/\' + name;
                    }
                // ]]></script>';

                    $request = $db->query('', '
                        SELECT * FROM {db_prefix}tp_categories
                        WHERE id = {int:varid} LIMIT 1',
                        array('varid' => $ccat)
                    );
                    if($db->num_rows($request) > 0) {
                        $row = $db->fetch_assoc($request);
                        $o = explode('|', $row['settings']);
                        foreach($o as $t => $opt) {
                            $b = explode('=', $opt);
                            if(isset($b[1])) {
                                $row[$b[0]] = $b[1];
                            }
                        }
                        $db->free_result($request);
                        $check = array('layout', 'catlayout', 'toppanel', 'bottompanel', 'leftpanel', 'rightpanel', 'upperpanel', 'lowerpanel', 'showchild');
                        foreach($check as $c => $ch) {
                            if(!isset($row[$ch])) {
                                $row[$ch] = 0;
                            }
                        }
                        $context['TPortal']['editcategory'] = $row;
                    }
                    // fetch all categories and subcategories
                    $request = $db->query('', '
                        SELECT	id, display_name as name, parent as parent, access, dt_log,
                            page, settings, short_name
                        FROM {db_prefix}tp_categories
                        WHERE item_type = {string:type}',
                        array('type' => 'category')
                    );

                    $context['TPortal']['editcats'] = array();
                    $allsorted = array();
                    $alcats = array();
                    if($db->num_rows($request) > 0) {
                        while ($row = $db->fetch_assoc($request)) {
                            $row['indent'] = 0;
                            $allsorted[$row['id']] = $row;
                            $alcats[] = $row['id'];
                        }
                        $db->free_result($request);
                        if(count($allsorted) > 1) {
                            $context['TPortal']['editcats'] = chain('id', 'parent', 'name', $allsorted);
                        }
                        else {
                            $context['TPortal']['editcats'] = $allsorted;
                        }
                    }
                    TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa=categories;cu='. $ccat, $txt['tp-editcategory']);
                }
                return;
            }

            // fetch all categories and subcategories
            $request = $db->query('', '
                SELECT id, display_name as name, parent as parent, access, dt_log,
                    page, settings, short_name
                FROM {db_prefix}tp_categories
                WHERE item_type = {string:type}',
                array('type' => 'category')
            );

            $context['TPortal']['editcats'] = array();
            $allsorted = array();
            $alcats = array();
            if($db->num_rows($request) > 0) {
                while ($row = $db->fetch_assoc($request)) {
                    $row['indent'] = 0;
                    $allsorted[$row['id']] = $row;
                    $alcats[] = $row['id'];
                }
                $db->free_result($request);
                if(count($allsorted) > 1) {
                    $context['TPortal']['editcats'] = chain('id', 'parent', 'name', $allsorted);
                }
                else {
                    $context['TPortal']['editcats'] = $allsorted;
                }
            }
            // get the filecount as well
            if(count($alcats) > 0) {
                $request = $db->query('', '
                    SELECT	art.category as id, COUNT(art.id) as files
                    FROM {db_prefix}tp_articles as art
                    WHERE art.category IN ({array_int:cats})
                    GROUP BY art.category',
                    array('cats' => $alcats)
                );

                if($db->num_rows($request) > 0) {
                    $context['TPortal']['cats_count'] = array();
                    while ($row = $db->fetch_assoc($request)) {
                        $context['TPortal']['cats_count'][$row['id']] = $row['files'];
                    }
                    $db->free_result($request);
                }
            }
            if($context['TPortal']['subaction'] == 'addcategory') {
                TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa=addcategory', $txt['tp-addcategory']);
            }
            if($context['TPortal']['subaction'] == 'clist') {
                TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa=clist', $txt['tp-tabs11']);
            }

            return;
        }
        TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa=articles', $txt['tp-articles']);
        // are we inside a category?
        if(isset($_GET['cu']) && is_numeric($_GET['cu'])) {
            $where = $_GET['cu'];
        }
        // show the no category articles?
        if(isset($_GET['sa']) && $_GET['sa'] == 'strays') {
            TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa=strays', $txt['tp-strays']);
            $show_nocategory = true;
        }

        // submissions?
        if(isset($_GET['sa']) && $_GET['sa'] == 'submission') {
            TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa=submission', $txt['tp-submissions']);
            $show_submission = true;
        }

        // single article?
        if(isset($_GET['sa']) && substr($_GET['sa'], 0, 11) == 'editarticle') {
            $whatarticle = TPUtil::filter('article', 'get', 'string');
            TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa='.$_GET['sa'].';article='.$whatarticle, $txt['tp-editarticle']);
        }
        // are we starting a new one?
        if(isset($_GET['sa']) && substr($_GET['sa'], 0, 11) == 'addarticle_') {
            TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa='.$_GET['sa'], $txt['tp-addarticle']);
            $context['TPortal']['editarticle'] = array(
                'id' => '',
                'date' => time(),
                'body' => '',
                'intro' => '',
                'useintro' => 0,
                'category' => !empty($_GET['cu']) ? $_GET['cu'] : 0,
                'frontpage' => 1,
                'author_id' => $context['user']['id'],
                'subject' => '',
                'author' => $context['user']['name'],
                'frame' => 'theme',
                'approved' => 0,
                'off' => 1,
                'options' => 'date,title,author,linktree,top,cblock,rblock,lblock,bblock,tblock,lbblock,category,catlist,comments,commentallow,commentupshrink,views,rating,ratingallow,avatar,inherit,social,nofrontsetting',
                'parse' => 0,
                'comments' => 0,
                'comments_var' => '',
                'views' => 0,
                'rating' => 0,
                'voters' => '',
                'id_theme' => 0,
                'shortname' => '',
                'sticky' => 0,
                'fileimport' => '',
                'topic' => 0,
                'locked' => 0,
                'illustration' => '',
                'headers' => '',
                'type' => substr($_GET['sa'],11),
                'featured' => 0,
                'real_name' => $context['user']['name'],
                'author_id' => $context['user']['id'],
                'articletype' => substr($_GET['sa'],11),
                'id_theme' => 0,
                'pub_start' => 0,
                'pub_end' => 0,
            );
            $context['html_headers'] .= '
                <script type="text/javascript"><!-- // --><![CDATA[
                    function changeIllu(node,name)
                    {
                        node.src = \'' . $boardurl . '/tp-files/tp-articles/illustrations/\' + name;
                    }
                // ]]></script>';
            // Add in BBC editor before we call in template so the headers are there
            if(substr($_GET['sa'], 11) == 'bbc') {
                $context['TPortal']['editor_id'] = 'tp_article_body';
                TP_prebbcbox($context['TPortal']['editor_id']);
            }
        }

        // fetch categories and subcategories
        if(!isset($show_nocategory)) {
            $request = $db->query('', '
                SELECT DISTINCT var.id AS id, var.display_name AS name, var.parent AS parent
                FROM {db_prefix}tp_categories AS var
                WHERE var.item_type = {string:type}
                ' . (isset($where) ? 'AND var.parent'.((TP_PGSQL == true) ? '::Integer' : ' ' ).' = {int:whereval}' : '') . '
                ORDER BY parent, id DESC',
                array('type' => 'category', 'whereval' => isset($where) ? $where : 0)
            );

            if($db->num_rows($request) > 0) {
                $context['TPortal']['basecats'] = isset($where) ? array($where) : array('0', '9999');
                $cats = array();
                $context['TPortal']['cats'] = array();
                $sorted = array();
                while ($row = $db->fetch_assoc($request)) {
                    $sorted[$row['id']] = $row;
                    $cats[] = $row['id'];
                }
                $db->free_result($request);
                if(count($sorted) > 1) {
                    $context['TPortal']['cats'] = chain('id', 'parent', 'name', $sorted);
                }
                else {
                    $context['TPortal']['cats'] = $sorted;
                }
            }
        }

        if(isset($show_submission) && $context['TPortal']['total_submissions'] > 0) {
            // check if we have any start values
            $start = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? $_GET['p'] : 0;
            // sorting?
            $sort = $context['TPortal']['sort'] = (!empty($_GET['sort']) && in_array($_GET['sort'], array('date', 'id','author_id', 'type', 'subject', 'parse'))) ? $_GET['sort'] : 'date';
            $context['TPortal']['pageindex'] = TPageIndex($scripturl . '?action=admin;area=tparticles;sa=submission;sort=' . $sort , $start, $context['TPortal']['total_submissions'], 15);
            $request = $db->query('', '
                SELECT	art.id, art.date, art.frontpage, art.category, art.author_id as author_id,
                    COALESCE(mem.real_name, art.author) as author, art.subject, art.approved,
                    art.sticky, art.type, art.featured, art.locked, art.off, art.parse as pos
                FROM {db_prefix}tp_articles AS art
                LEFT JOIN {db_prefix}members AS mem ON (art.author_id = mem.id_member)
                WHERE art.approved = {int:approved}
                ORDER BY art.{raw:col} {raw:sort}
                LIMIT {int:start}, 15',
                array(
                    'approved' => 0,
                    'col' => $sort,
                    'start' => $start,
                    'sort' => in_array($sort, array('sticky', 'locked', 'frontpage', 'date', 'active')) ? 'DESC' : 'ASC',
                )
            );

            if($db->num_rows($request) > 0) {
                $context['TPortal']['arts_submissions']=array();
                while ($row = $db->fetch_assoc($request)) {
                    $context['TPortal']['arts_submissions'][] = $row;
                }
                $db->free_result($request);
            }
        }

        if(isset($show_nocategory) && $context['TPortal']['total_nocategory'] > 0) {
            // check if we have any start values
            $start = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? $_GET['p'] : 0;
            // sorting?
            $sort = $context['TPortal']['sort'] = (!empty($_GET['sort']) && in_array($_GET['sort'], array('off', 'date', 'id', 'author_id', 'locked', 'frontpage', 'sticky', 'featured', 'type', 'subject', 'parse'))) ? $_GET['sort'] : 'date';
            $context['TPortal']['pageindex'] = TPageIndex($scripturl . '?action=admin;area=tparticles;sa=articles;sort=' . $sort , $start, $context['TPortal']['total_nocategory'], 15);
            $request = $db->query('', '
                SELECT	art.id, art.date, art.frontpage, art.category, art.author_id as author_id,
                    COALESCE(mem.real_name, art.author) as author, art.subject, art.approved, art.sticky,
                    art.type, art.featured,art.locked, art.off, art.parse as pos
                FROM {db_prefix}tp_articles AS art
                LEFT JOIN {db_prefix}members AS mem ON (art.author_id = mem.id_member)
                WHERE (art.category = 0 OR art.category = 9999)
                ORDER BY art.{raw:col} {raw:sort}
                LIMIT {int:start}, 15',
                array(
                    'col' => $sort,
                    'sort' => in_array($sort, array('sticky', 'locked', 'frontpage', 'date', 'active')) ? 'DESC' : 'ASC',
                    'start' => $start,
                )
            );

            if($db->num_rows($request) > 0) {
                $context['TPortal']['arts_nocat'] = array();
                while ($row = $db->fetch_assoc($request)) {
                    $context['TPortal']['arts_nocat'][] = $row;
                }
                $db->free_result($request);
            }
        }
        // ok, fetch single article
        if(isset($whatarticle)) {
            $request = $db->query('', '
                SELECT	art.*,  COALESCE(mem.real_name, art.author) AS real_name, art.author_id AS author_id,
                    art.type as articletype, art.id_theme as id_theme
                FROM {db_prefix}tp_articles as art
                LEFT JOIN {db_prefix}members as mem ON (art.author_id = mem.id_member)
                WHERE art.id = {int:artid}',
                array(
                    'artid' => is_numeric($whatarticle) ? $whatarticle : 0,
                )
            );

            if($db->num_rows($request) > 0) {
                $context['TPortal']['editarticle'] = $db->fetch_assoc($request);
                $context['TPortal']['editing_article'] = true;
                $context['TPortal']['editarticle']['body'] = TPUtil::htmlspecialchars($context['TPortal']['editarticle']['body'], ENT_QUOTES);
                $db->free_result($request);
            }

            if($context['TPortal']['editarticle']['articletype'] == 'html') {
                TPwysiwyg_setup();
            }

            // Add in BBC editor before we call in template so the headers are there
            if($context['TPortal']['editarticle']['articletype'] == 'bbc') {
                $context['TPortal']['editor_id'] = 'tp_article_body';
                TP_prebbcbox($context['TPortal']['editor_id'], strip_tags($context['TPortal']['editarticle']['body']));
            }

            $context['TPortal']['editorchoice'] = 1;

            $context['html_headers'] .= '
                <script type="text/javascript"><!-- // --><![CDATA[
                    function changeIllu(node,name)
                    {
                        node.src = \'' . $boardurl . '/tp-files/tp-articles/illustrations/\' + name;
                    }
                // ]]></script>';

        }
        // fetch article count for these
        if(isset($cats)) {
            $request = $db->query('', '
                SELECT	art.category as id, COUNT(art.id) as files
                FROM {db_prefix}tp_articles as art
                WHERE art.category IN ({array_int:cat})
                GROUP BY art.category',
                array('cat' => $cats)
            );

            $context['TPortal']['cats_count'] = array();
            if($db->num_rows($request) > 0) {
                while ($row = $db->fetch_assoc($request))
                    $context['TPortal']['cats_count'][$row['id']] = $row['files'];
                $db->free_result($request);
            }
        }
        // get the icons needed
        tp_collectArticleIcons();

        // fetch all categories and subcategories
        $request = $db->query('', '
            SELECT	id, display_name as name, parent as parent
            FROM {db_prefix}tp_categories
            WHERE item_type = {string:type}',
            array('type' => 'category')
        );

        $context['TPortal']['allcats'] = array();
        $allsorted = array();

        if($db->num_rows($request) > 0) {
            while ($row = $db->fetch_assoc($request)) {
                $allsorted[$row['id']] = $row;
            }

            $db->free_result($request);
            if(count($allsorted) > 1) {
                $context['TPortal']['allcats'] = chain('id', 'parent', 'name', $allsorted);
            }
            else {
                $context['TPortal']['allcats'] = $allsorted;
            }
        }
        // not quite done yet lol, now we need to sort out if articles are to be listed
        if(isset($where)) {
            // check if we have any start values
            $start = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? $_GET['p'] : 0;
            // sorting?
            $sort = $context['TPortal']['sort'] = (!empty($_GET['sort']) && in_array($_GET['sort'], array('off', 'date', 'id', 'author_id' , 'locked', 'frontpage', 'sticky', 'featured', 'type', 'subject', 'parse'))) ? $_GET['sort'] : 'date';
            $context['TPortal']['categoryID'] = $where;
            // get the name
            $request = $db->query('', '
                SELECT display_name
                FROM {db_prefix}tp_categories
                WHERE id = {int:varid} LIMIT 1',
                array(
                    'varid' => $where
                )
            );
            $f = $db->fetch_assoc($request);
            $db->free_result($request);
            $context['TPortal']['categoryNAME'] = $f['display_name'];
            // get the total first
            $request = $db->query('', '
                SELECT	COUNT(*) as total
                FROM {db_prefix}tp_articles
                WHERE category = {int:cat}',
                array(
                    'cat' => $where
                )
            );

            $row = $db->fetch_assoc($request);
            $context['TPortal']['pageindex'] = TPageIndex($scripturl . '?action=admin;area=tparticles;sa=articles;sort=' . $sort . ';cu=' . $where, $start, $row['total'], 15);
            $db->free_result($request);

            $request = $db->query('', '
                SELECT art.id, art.date, art.frontpage, art.category, art.author_id AS author_id,
                    COALESCE(mem.real_name, art.author) AS author, art.subject, art.approved, art.sticky,
                    art.type, art.featured, art.locked, art.off, art.parse AS pos
                FROM {db_prefix}tp_articles AS art
                LEFT JOIN {db_prefix}members AS mem ON (art.author_id = mem.id_member)
                WHERE art.category = {int:cat}
                ORDER BY art.{raw:sort} {raw:sorter}
                LIMIT 15 OFFSET {int:start}',
                array('cat' => $where,
                    'sort' => $sort,
                    'sorter' => in_array($sort, array('sticky', 'locked', 'frontpage', 'date', 'active')) ? 'DESC' : 'ASC',
                    'start' => $start
                )
            );
            TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa=articles;cu='.$where, $txt['tp-blocktype19']);

            if($db->num_rows($request) > 0) {
                $context['TPortal']['arts']=array();
                while ($row = $db->fetch_assoc($request)) {
                    $context['TPortal']['arts'][] = $row;
                }
                $db->free_result($request);
            }
        }

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

        $context['html_headers'] .= '
        <script type="text/javascript" src="'. $settings['default_theme_url']. '/scripts/editor.js?rc1"></script>
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
                    if (img[i].className == "toggleFront")
                        img[i].onclick = toggleFront;
                    else if (img[i].className == "toggleSticky")
                        img[i].onclick = toggleSticky;
                    else if (img[i].className == "toggleLock")
                        img[i].onclick = toggleLock;
                    else if (img[i].className == "toggleActive")
                        img[i].onclick = toggleActive;
                    else if (img[i].className == "toggleFeatured")
                        img[i].onclick = toggleFeatured;
                }
            }

            function toggleActive(e)
            {
                var e = e ? e : window.event;
                var target = e.target ? e.target : e.srcElement;

                while(target.className != "toggleActive")
                      target = target.parentNode;

                var id = target.id.replace("artActive", "");
                var Ajax = getXMLHttpRequest();

                Ajax.open("POST", "?action=admin;area=tparticles;arton=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'");
                Ajax.setRequestHeader("Content-type", "application/x-www-form-urlencode");

                var source = target.src;
                target.src = "' . $settings['tp_images_url'] . '/ajax.gif"

                Ajax.onreadystatechange = function()
                {
                    if(Ajax.readyState == 4)
                    {
                        target.src = source == "' . $settings['tp_images_url'] . '/TPactive2.png" ? "' . $settings['tp_images_url'] . '/TPactive1.png" : "' . $settings['tp_images_url'] . '/TPactive2.png";
                    }
                }

                var params = "?action=admin;area=tparticles;arton=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'";
                Ajax.send(params);
            }
            function toggleFront(e)
            {
                var e = e ? e : window.event;
                var target = e.target ? e.target : e.srcElement;

                while(target.className != "toggleFront")
                      target = target.parentNode;

                var id = target.id.replace("artFront", "");
                var Ajax = getXMLHttpRequest();

                Ajax.open("POST", "?action=admin;area=tparticles;artfront=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'");
                Ajax.setRequestHeader("Content-type", "application/x-www-form-urlencode");

                var source = target.src;
                target.src = "' . $settings['tp_images_url'] . '/ajax.gif"

                Ajax.onreadystatechange = function()
                {
                    if(Ajax.readyState == 4)
                    {
                        target.src = source == "' . $settings['tp_images_url'] . '/TPfront.png" ? "' . $settings['tp_images_url'] . '/TPfront2.png" : "' . $settings['tp_images_url'] . '/TPfront.png";
                    }
                }

                var params = "?action=admin;area=tparticles;artfront=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'";
                Ajax.send(params);
            }
            function toggleSticky(e)
            {
                var e = e ? e : window.event;
                var target = e.target ? e.target : e.srcElement;

                while(target.className != "toggleSticky")
                      target = target.parentNode;

                var id = target.id.replace("artSticky", "");
                var Ajax = getXMLHttpRequest();

                Ajax.open("POST", "?action=admin;area=tparticles;artsticky=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'");
                Ajax.setRequestHeader("Content-type", "application/x-www-form-urlencode");

                var source = target.src;
                target.src = "' . $settings['tp_images_url'] . '/ajax.gif"

                Ajax.onreadystatechange = function()
                {
                    if(Ajax.readyState == 4)
                    {
                        target.src = source == "' . $settings['tp_images_url'] . '/TPsticky1.png" ? "' . $settings['tp_images_url'] . '/TPsticky2.png" : "' . $settings['tp_images_url'] . '/TPsticky1.png";
                    }
                }

                var params = "?action=admin;area=tparticles;artsticky=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'";
                Ajax.send(params);
            }
            function toggleLock(e)
            {
                var e = e ? e : window.event;
                var target = e.target ? e.target : e.srcElement;

                while(target.className != "toggleLock")
                      target = target.parentNode;

                var id = target.id.replace("artLock", "");
                var Ajax = getXMLHttpRequest();

                Ajax.open("POST", "?action=admin;area=tparticles;artlock=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'");
                Ajax.setRequestHeader("Content-type", "application/x-www-form-urlencode");

                var source = target.src;
                target.src = "' . $settings['tp_images_url'] . '/ajax.gif"

                Ajax.onreadystatechange = function()
                {
                    if(Ajax.readyState == 4)
                    {
                        target.src = source == "' . $settings['tp_images_url'] . '/TPlock1.png" ? "' . $settings['tp_images_url'] . '/TPlock2.png" : "' . $settings['tp_images_url'] . '/TPlock1.png";
                    }
                }

                var params = "?action=admin;area=tparticles;artlock=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'";
                Ajax.send(params);
            }
            function toggleFeatured(e)
            {
                var e = e ? e : window.event;
                var target = e.target ? e.target : e.srcElement;

                var aP=document.getElementsByTagName(\'img\');
                for(var i=0; i<aP.length; i++)
                {
                    if(aP[i].className===\'toggleFeatured\' && aP[i] != target)
                    {
                        aP[i].src=\'' . $settings['tp_images_url'] . '/TPflag2.png\';
                    }
                }


                while(target.className != "toggleFeatured")
                      target = target.parentNode;

                var id = target.id.replace("artFeatured", "");
                var Ajax = getXMLHttpRequest();

                Ajax.open("POST", "?action=admin;area=tparticles;artfeat=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'");
                Ajax.setRequestHeader("Content-type", "application/x-www-form-urlencode");

                var source = target.src;
                target.src = "' . $settings['tp_images_url'] . '/ajax.gif"

                Ajax.onreadystatechange = function()
                {
                    if(Ajax.readyState == 4)
                    {
                        target.src = source == "' . $settings['tp_images_url'] . '/TPflag.png" ? "' . $settings['tp_images_url'] . '/TPflag2.png" : "' . $settings['tp_images_url'] . '/TPflag.png";
                    }
                }

                var params = "?action=admin;area=tparticles;artfeat=" + id + ";' . $context['session_var'] . '=' . $context['session_id'].'";
                Ajax.send(params);
            }
        // ]]></script>';

        if($context['TPortal']['subaction'] == 'artsettings') {
            TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa=artsettings', $txt['tp-settings']);
        }
        elseif($context['TPortal']['subaction'] == 'articons') {
            TPadd_linktree($scripturl.'?action=admin;area=tparticles;sa=articons', $txt['tp-adminicons']);
        }

    }}}

    public function do_postchecks() {{{
        global $context, $txt, $settings;

        $db = TPDatabase::getInstance();

        // If we have any setting changes add them to this array
        $updateArray = array();
        // which screen do we come from?
        if(!empty($_POST['tpadmin_form']))
        {
            // get it
            $from = $_POST['tpadmin_form'];
            // block permissions overview
            if(in_array($from, array('artsettings')))
            {
                checkSession('post');
                isAllowedTo('tp_settings');
                $w = array();
                $ssi = array();

                switch($from) {
                    case 'artsettings':
                        $checkboxes = array('use_wysiwyg', 'use_dragdrop', 'hide_editarticle_link', 'print_articles', 'allow_links_article_comments', 'hide_article_facebook', 'hide_article_twitter', 'hide_article_reddit', 'hide_article_digg', 'hide_article_delicious', 'hide_article_stumbleupon');
                        foreach($checkboxes as $v) {
                            if(TPUtil::checkboxChecked('tp_'.$v)) {
                                $updateArray[$v] = "1";
                            }
                            else {
                                $updateArray[$v] = "";
                            }
                            // remove the variable so we don't process it twice before the old logic is removed
                            unset($_POST['tp_'.$v]);
                        }
                        break;
                    default:
                        break;
                }

                foreach($_POST as $what => $value) {
                    if(substr($what, 0, 3) == 'tp_') {
                        $where = substr($what, 3);
                        $clean = $value;
                        if(isset($clean)) {
                            $updateArray[$where] = $clean;
                        }
                    }
                }

                updateTPSettings($updateArray);
                return $from;
            }
            // categories
            elseif($from == 'categories')
            {
                checkSession('post');
                isAllowedTo('tp_articles');

                foreach($_POST as $what => $value)
                {
                    if(substr($what, 0, 3) == 'tp_')
                    {
                        if($from == 'categories')
                        {
                            if(substr($what, 0, 19) == 'tp_category_parent_')
                            {
                                $where = substr($what, 19);
                                //make sure parent are not its own parent
                                $request = $db->query('', '
                                    SELECT parent FROM {db_prefix}tp_categories
                                    WHERE id = {string:varid} LIMIT 1',
                                    array(
                                        'varid' => $value
                                    )
                                );
                                $row = $db->fetch_assoc($request);
                                $db->free_result($request);
                                if($row['parent'] == $where)
                                    $db->query('', '
                                        UPDATE {db_prefix}tp_categories
                                        SET parent = {string:val2}
                                        WHERE id = {string:varid}',
                                        array(
                                            'val2' => '0',
                                            'varid' => $value,
                                        )
                                    );

                                $db->query('', '
                                    UPDATE {db_prefix}tp_categories
                                    SET parent = {string:val2}
                                    WHERE id = {string:varid}',
                                    array(
                                        'val2' => $value,
                                        'varid' => $where,
                                    )
                                );
                            }
                        }
                    }
                }
                return $from;
            }
            // articles
            elseif($from == 'articles')
            {
                checkSession('post');
                isAllowedTo('tp_articles');

                foreach($_POST as $what => $value)
                {
                    if(substr($what, 0, 14) == 'tp_article_pos')
                    {
                        $where = substr($what, 14);
                            $db->query('', '
                                UPDATE {db_prefix}tp_articles
                                SET parse = {int:parse}
                                WHERE id = {int:artid}',
                                array(
                                    'parse' => $value,
                                    'artid' => $where,
                                )
                            );
                    }
                }
                if(isset($_POST['tpadmin_form_category']) && is_numeric($_POST['tpadmin_form_category']))
                    return $from.';cu=' . $_POST['tpadmin_form_category'];
                else
                    return $from;
            }
            // add a category
            elseif($from == 'addcategory')
            {
                checkSession('post');
                isAllowedTo('tp_articles');
                $name = !empty($_POST['tp_cat_name']) ? $_POST['tp_cat_name'] : $txt['tp-noname'];
                $parent = !empty($_POST['tp_cat_parent']) ? $_POST['tp_cat_parent'] : '0';
                $shortname = !empty($_POST['tp_cat_shortname']) ? $_POST['tp_cat_shortname'] : '';

                $db->insert('INSERT',
                    '{db_prefix}tp_categories',
                    array(
                        'display_name' => 'string',
                        'parent' => 'string',
                        'access' => 'string',
                        'item_type' => 'string',
                        'dt_log' => 'string',
                        'page' => 'int',
                        'settings' => 'string',
                        'short_name' => 'string',
                    ),
                    array(strip_tags($name), $parent, '', 'category', '', 0, 'sort=date|sortorder=desc|articlecount=5|layout=1|catlayout=1|showchild=0|leftpanel=1|rightpanel=1|toppanel=1|centerpanel=1|lowerpanel=1|bottompanel=1', $shortname),
                    array('id')
                );

                $go = $db->insert_id('{db_prefix}tp_categories', 'id');
                redirectexit('action=admin;area=tparticles;sa=categories;cu='.$go);
            }
            // the categort list
            elseif($from == 'clist') {
                checkSession('post');
                isAllowedTo('tp_articles');

                $cats = array();
                foreach($_POST as $what => $value)
                {
                    if(substr($what, 0, 8) == 'tp_clist')
                        $cats[] = $value;
                }
                if(sizeof($cats) > 0)
                    $catnames = implode(',', $cats);
                else
                    $catnames = '';

                $updateArray['cat_list'] = $catnames;

                updateTPSettings($updateArray);

                return $from;
            }

            // edit a category
            elseif($from == 'editcategory')
            {
                checkSession('post');
                isAllowedTo('tp_articles');

                $options = array();
                $groups = array();
                $where = $_POST['tpadmin_form_id'];
                foreach($_POST as $what => $value)
                {
                    if(substr($what, 0, 3) == 'tp_')
                    {
                        $clean = $value;
                        $param = substr($what, 12);
                        if(in_array($param, array('page', 'short_name')))
                            $db->query('', '
                                UPDATE {db_prefix}tp_categories
                                SET '. $param .' = {string:val}
                                WHERE id = {int:varid}',
                                array('val' => $value, 'varid' => $where)
                            );
                        // parents needs some checking..
                        elseif($param == 'parent')
                        {
                            //make sure parent are not its own parent
                            $request = $db->query('', '
                                SELECT parent FROM {db_prefix}tp_categories
                                WHERE id = {int:varid} LIMIT 1',
                                array('varid' => $value)
                            );
                            $row = $db->fetch_assoc($request);
                            $db->free_result($request);
                            if(isset($row['parent']) && ( $row['parent'] == $where ))
                                $db->query('', '
                                    UPDATE {db_prefix}tp_categories
                                    SET parent = {string:val2}
                                    WHERE id = {int:varid}',
                                    array('val2' => '0', 'varid' => $value)
                                );

                            $db->query('', '
                                UPDATE {db_prefix}tp_categories
                                SET parent = {string:val2}
                                WHERE id = {int:varid}',
                                array('val2' => $value, 'varid' => $where)
                            );
                        }
                        elseif($param == 'display_name')
                            $db->query('', '
                                UPDATE {db_prefix}tp_categories
                                SET display_name = {string:val1}
                                WHERE id = {int:varid}',
                                array('val1' => strip_tags($value), 'varid' => $where)
                            );
                        elseif($param == 'dt_log')
                            $db->query('', '
                                UPDATE {db_prefix}tp_categories
                                SET dt_log = {string:val4}
                                WHERE id = {int:varid}',
                                array('val4' => $value, 'varid' => $where)
                            );
                        elseif($param == 'custom_template')
                            $db->query('', '
                                UPDATE {db_prefix}tp_categories
                                SET custom_template = {string:val9}
                                WHERE id = {int:varid}',
                                array('val9' => $value, 'varid' => $where)
                            );
                        elseif(substr($param, 0, 6) == 'group_')
                            $groups[] = substr($param, 6);
                        else
                            $options[] = $param. '=' . $value;
                    }
                }
                $db->query('', '
                    UPDATE {db_prefix}tp_categories
                    SET access = {string:val3}, settings = {string:val7}
                    WHERE id = {int:varid}',
                    array('val3' => implode(',', $groups), 'val7' => implode('|', $options), 'varid' => $where)
                );
                $from = 'categories;cu=' . $where;
                return $from;
            }
            // stray articles
            elseif($from == 'strays')
            {
                checkSession('post');
                isAllowedTo('tp_articles');

                $ccats = array();
                // check if we have some values
                foreach($_POST as $what => $value)
                {
                    if(substr($what, 0, 16) == 'tp_article_stray')
                        $ccats[] = substr($what, 16);
                    elseif($what == 'tp_article_cat')
                        $straycat = $value;
                    elseif($what == 'tp_article_new')
                        $straynewcat = $value;
                }
                // update
                if(isset($straycat) && sizeof($ccats) > 0)
                {
                    $category = $straycat;
                    if($category == 0 && !empty($straynewcat))
                    {
                        $request = $db->insert('INSERT',
                            '{db_prefix}tp_categories',
                            array('display_name' => 'string', 'parent' => 'string', 'item_type' => 'string'),
                            array(strip_tags($straynewcat), '0', 'category'),
                            array('id')
                        );

                        $newcategory = $db->insert_id('{db_prefix}tp_categories', 'id');
                        $db->free_result($request);
                    }
                    $db->query('', '
                        UPDATE {db_prefix}tp_articles
                        SET category = {int:cat}
                        WHERE id IN ({array_int:artid})',
                        array(
                            'cat' => !empty($newcategory) ? $newcategory : $category,
                            'artid' => $ccats,
                        )
                    );
                }
                return $from;
            }
            // from articons...
            elseif($from == 'articons')
            {
                checkSession('post');
                isAllowedTo('tp_articles');

                if(file_exists($_FILES['tp_article_newillustration']['tmp_name']))
                {
                    $name = TPuploadpicture('tp_article_newillustration', '', $context['TPortal']['icon_max_size'], 'jpg,gif,png', 'tp-files/tp-articles/illustrations');
                    tp_createthumb('tp-files/tp-articles/illustrations/'. $name, $context['TPortal']['icon_width'], $context['TPortal']['icon_height'], 'tp-files/tp-articles/illustrations/s_'. $name);
                    unlink('tp-files/tp-articles/illustrations/'. $name);
                }
                // how about deleted?
                foreach($_POST as $what => $value)
                {
                    if(substr($what, 0, 15) == 'artillustration')
                        unlink(BOARDDIR.'/tp-files/tp-articles/illustrations/'.$value);
                }
                return $from;
            }
            // submitted ones
            elseif($from == 'submission')
            {
                checkSession('post');
                isAllowedTo('tp_articles');

                $ccats = array();
                // check if we have some values
                foreach($_POST as $what => $value)
                {
                    if(substr($what, 0, 21) == 'tp_article_submission')
                        $ccats[] = substr($what,21);
                    elseif($what == 'tp_article_cat')
                        $straycat = $value;
                    elseif($what == 'tp_article_new')
                        $straynewcat = $value;
                }
                // update
                if(isset($straycat) && sizeof($ccats) > 0)
                {
                    $category = $straycat;
                    if($category == 0 && !empty($straynewcat))
                    {
                        $request = $db->insert('INSERT',
                            '{db_prefix}tp_categories',
                            array(
                                'display_name' => 'string',
                                'parent' => 'string',
                                'item_type' => 'string',
                            ),
                            array($straynewcat, '0', 'category'),
                            array('id')
                        );

                        $newcategory = $db->insert_id('{db_prefix}tp_categories', 'id');
                        $db->free_result($request);
                    }
                    $db->query('', '
                        UPDATE {db_prefix}tp_articles
                        SET approved = {int:approved}, category = {int:cat}
                        WHERE id IN ({array_int:artid})',
                        array(
                            'approved' => 1,
                            'cat' => !empty($newcategory) ? $newcategory : $category,
                            'artid' => $ccats,
                        )
                    );
                }
                return $from;
            }
            // Editing an article?
            elseif(substr($from, 0, 11) == 'editarticle') {
                return $this->articleEdit();
            }
        }
        else {
            return;
        }

    }}}

}

?>
