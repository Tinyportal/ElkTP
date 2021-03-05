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
namespace TinyPortal\Controller;

use \TinyPortal\Model\Article as TPArticle;
use \TinyPortal\Model\Block as TPBlock;
use \TinyPortal\Model\Database as TPDatabase;
use \TinyPortal\Model\Integrate as TPIntegrate;
use \TinyPortal\Model\Mentions as TPMentions;
use \TinyPortal\Model\Permissions as TPPermissions;
use \TinyPortal\Model\Subs as TPSubs;
use \TinyPortal\Model\Util as TPUtil;
use \ElkArte\Errors\Errors;
use \ElkArte\sources\Frontpage_Interface;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Portal extends \Action_Controller implements Frontpage_Interface
{

    public static function canFrontPage() {{{
        return true;
    }}}

    public static function frontPageOptions() {{{
        return true;
	}}}

    public static function frontPageHook(&$default_action) {{{

        // View the portal front page
        $file       = __FILE__;
        $controller = '\TinyPortal\Controller\Portal';
        $function   = 'action_index';
        // Something article-ish, then set the new action
        if (isset($file, $function)) {
            $default_action = array(
                'file' => $file,
                'controller' => isset($controller) ? $controller : null,
                'function' => $function
            );
        }

    }}}

	public static function validateFrontPageOptions($post) {{{
        return true;
    }}}

    public function trackStats($stats = array()) {{{


    }}}

    public function action_index() {{{
        global $context, $txt;

		TPSubs::getInstance()->loadLanguage('TPortal');

        $action = TPUtil::filter('action', 'get', 'string');
        if($action == 'tportal') {
            $subAction  = TPUtil::filter('sa', 'get', 'string');
            if($subAction == false) {
                throw new Elk_Exception($txt['tp-no-sa-url'], 'general');
            }

            $subActions = array(
                'credits'   => array($this, 'action_credits', array()),
                'upshrink'  => array($this, 'action_upshrink', array()),
            );

            call_integration_hook('integrate_tp_pre_subactions', array(&$subActions));

            if(!array_key_exists($subAction, $subActions)) {
                throw new Elk_Exception($txt['tp-no-sa-list'], 'general');
            }

            $context['TPortal']['subaction'] = $subAction;

            $action     = new \Action();
            $sa         = $action->initialize($subActions, $subAction);
            $action->dispatch($sa);

            call_integration_hook('integrate_tp_post_subactions');
        }
        else {
            if(TPUtil::filter('page', 'get', 'string') && !isset($context['current_action'])) {
                $context['shortID'] = self::action_page();
            }
            else if(TPUtil::filter('cat', 'get', 'string')) {
                $context['catshortID'] = self::action_category();
            }
            else if(!(TPUtil::filter('action', 'get', 'string') && TPUtil::filter('board', 'get', 'string') && TPUtil::filter('topic', 'get', 'string'))) {
                self::action_frontpage();
            }
        }

    }}}

    function action_page() {{{
        global $context, $scripturl, $txt, $modSettings, $user_info;

        \loadTemplate('TPortal');

        $db = TPDatabase::getInstance();
        $now = time();
        // Set the avatar height/width
        $avatar_width = '';
        $avatar_height = '';
        if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize') {
            $avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
            $avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
        }

        // check validity and fetch it
        if($page = TPUtil::filter('page', 'get', 'string')) {

            $_SESSION['login_url'] = $scripturl . '?page=' . $page;

            $tpArticle  = TPArticle::getInstance();
            $article    = $tpArticle->getArticle($page);
            // We only want the first article
            if(!empty($article) && isset($article[0])) {
                $article = $article[0];
            }

            if(is_array($article) && !empty($article)) {
                $shown  = false;
                $valid  = true;

                // if its not approved, say so.
                if($article['approved'] == 0) {
                    \ElkArte\Errors\Errors::instance()->log_error($txt['tp-notapproved'], 'general');
                    $shown = true;
                }

                // and for no category
                if( ( $article['category'] < 1 || $article['category'] > 9999 ) && $shown == false) {
                    \ElkArte\Errors\Errors::instance()->log_error($txt['tp-nocategory'], 'general');
                    $shown = true;
                }

                // likewise for off.
                if($article['off'] == 1 && $shown == false) {
                    \ElkArte\Errors\Errors::instance()->log_error($txt['tp-noton'], 'general');
                    $shown = true;
                }

                if($shown == true && !allowedTo('tp_articles')) {
                    $valid = false;
                }

                if( TPPermissions::getInstance()->getPermissions($article['access'], '') && $valid) {
                    // compability towards old articles
                    if(empty($article['type'])) {
                        $article['type'] = $article['rendertype'] = 'html';
                    }

                    // shortname title
                    $article['shortname'] = un_htmlspecialchars($article['shortname']);
                    // Add ratings together
                    $article['rating'] = array_sum(explode(',', $article['rating']));
                    // allowed and all is well, go on with it.
                    $context['TPortal']['article'] = $article;

                    $context['TPortal']['article']['avatar'] = determineAvatar( array(
                            'avatar'            => $article['avatar'],
                            'email_address'     => $article['email_address'],
                            'filename'          => !empty($article['filename']) ? $article['filename'] : '',
                            'id_attach'         => $article['id_attach'],
                            'attachment_type'   => $article['attachment_type'],
                         )
                    )['image'];

                    $tpArticle->updateViews($page);

                    $comments = $tpArticle->getArticleComments($context['user']['id'] , $article['id']);

                    $context['TPortal']['article']['countarticles'] = $tpArticle->getTotalAuthorArticles($context['TPortal']['article']['author_id'], true, true);

                    // We'll use this in the template to allow comment box
                    if (allowedTo('tp_artcomment')) {
                        $context['TPortal']['can_artcomment'] = true;
                    }

                    $context['TPortal']['article_comments_count']   = 0;
                    $context['TPortal']['article']['comment_posts'] = array();
                    if(is_array($comments)) {
                        $last = $comments['last'];
                        $context['TPortal']['article_comments_new']     = $comments['new_count'];
                        $context['TPortal']['article_comments_count']   = $comments['comment_count'];
                        unset($comments['last']);
                        unset($comments['new_count']);
                        unset($comments['comment_count']);

                        foreach($comments as $row) {

                            $avatar = determineAvatar( array(
                                        'avatar'            => $row['avatar'],
                                        'email_address'     => $row['email_address'],
                                        'filename'          => !empty($row['filename']) ? $row['filename'] : '',
                                        'id_attach'         => $row['id_attach'],
                                        'attachment_type'  => $row['attachment_type'],
                                    )
                            )['image'];

                            $context['TPortal']['article']['comment_posts'][] = array(
                                'id'        => $row['id'],
                                'subject'   => '<a href="'.$scripturl.'?page='.$context['TPortal']['article']['id'].'#comment'. $row['id'].'">'.$row['subject'].'</a>',
                                'text'      => parse_bbc($row['comment']),
                                'timestamp' => $row['datetime'],
                                'date'      => standardTime($row['datetime']),
                                'poster_id' => $row['member_id'],
                                'poster'    => $row['real_name'],
                                'is_new'    => ( $row['datetime'] > $last ) ? true : false,
                                'avatar' => array (
                                    'name' => &$row['avatar'],
                                    'image' => $avatar,
                                    'href'  => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? (empty($row['attachment_type']) ? $scripturl . '?action=tportal;sa=tpattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'https://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
                                    'url'   => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'https://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar'])
                                ),
                            );
                        }
                    }

                    // the frontblocks should not display here
                    $context['TPortal']['frontpanel'] = 0;
                    // sort out the options
                    $context['TPortal']['article']['visual_options'] = explode(',', $article['options']);

                    // the custom widths
                    foreach ($context['TPortal']['article']['visual_options'] as $pt) {
                        if(substr($pt, 0, 11) == 'lblockwidth') {
                            $context['TPortal']['blockwidth_left'] = substr($pt, 11);
                        }
                        if(substr($pt, 0, 11) == 'rblockwidth') {
                            $context['TPortal']['blockwidth_right'] = substr($pt, 11);
                        }
                    }
                    // check if no theme is to be applied
                    if(in_array('nolayer', $context['TPortal']['article']['visual_options'])) {
                        $context['template_layers'] = array('nolayer');
                        // add the headers!
                        $context['tp_html_headers'] .= $article['headers'];
                    }
                    // set bars on/off according to options, setting override
                    $all = array('showtop', 'centerpanel', 'leftpanel', 'rightpanel', 'toppanel', 'bottompanel', 'lowerpanel');
                    $all2=array('top', 'cblock', 'lblock', 'rblock', 'tblock', 'bblock', 'lbblock', 'comments', 'views', 'rating', 'date', 'title',
                    'commentallow', 'commentupshrink', 'ratingallow', 'nolayer', 'avatar');

                    for($p = 0; $p < 7; $p++) {
                        $primary = $context['TPortal'][$all[$p]];
                        if(in_array($all2[$p], $context['TPortal']['article']['visual_options'])) {
                            $secondary = 1;
                        }
                        else {
                            $secondary = 0;
                        }

                        if($primary == '1') {
                            $context['TPortal'][$all[$p]] = $secondary;
                        }
                    }
                    $ct = explode('|', $article['settings']);
                    $cat_opts = array();
                    foreach($ct as $cc => $val) {
                        $ts = explode('=', $val);
                        if(isset($ts[0]) && isset($ts[1])) {
                            $cat_opts[$ts[0]] = $ts[1];
                        }
                    }

                    // decide the template
                    if(isset($cat_opts['catlayout']) && $cat_opts['catlayout'] == 7) {
                        $cat_opts['template'] = $article['custom_template'];
                    }

                    $context['TPortal']['article']['category_opts'] = $cat_opts;

                    // the article should follow panel settngs from category?
                    if(in_array('inherit', $context['TPortal']['article']['visual_options'])) {
                        // set bars on/off according to options, setting override
                        $all = array('upperpanel', 'leftpanel', 'rightpanel', 'toppanel', 'bottompanel', 'lowerpanel');
                        for($p = 0; $p < 6; $p++) {
                            if(isset($cat_opts[$all[$p]])) {
                                $context['TPortal'][$all[$p]] = $cat_opts[$all[$p]];
                            }
                        }
                    }

                    // should we supply links to articles in same category?
                    if(in_array('category', $context['TPortal']['article']['visual_options'])) {
                        $request = $db->query('', '
                            SELECT id, subject, shortname
                            FROM {db_prefix}tp_articles
                            WHERE category = {int:cat}
                            AND off = 0
                            AND approved = 1
                            ORDER BY parse',
                            array('cat' => $context['TPortal']['article']['category'])
                        );
                        if($db->num_rows($request) > 0) {
                            $context['TPortal']['article']['others'] = array();
                            while($row = $db->fetch_assoc($request)) {
                                if($row['id'] == $context['TPortal']['article']['id']) {
                                    $row['selected'] = 1;
                                }

                                $context['TPortal']['article']['others'][] = $row;
                            }
                            $db->free_result($request);
                        }
                    }

                    // can we rate this article?
                    $context['TPortal']['article']['can_rate'] = in_array($context['user']['id'], explode(',', $article['voters'])) ? false : true;

                    // are we rather printing this article and printing page is allowed?
                    if(isset($_GET['print']) && $context['TPortal']['print_articles'] == 1) {
                        if(!isset($article['id'])) {
                            redirectexit();
                        }
                        $what = '<h2>' . $article['subject'] . ' </h2>'. $article['body'];
                        $pwhat = 'echo \'<h2>\' . $article[\'subject\'] . \'</h2>\';' . $article['body'];
                        if($article['type'] == 'php') {
                            $context['TPortal']['printbody'] = eval($pwhat);
                        }
                        elseif($article['type'] == 'import') {
                            if(!file_exists(BOARDDIR. '/' . $article['fileimport'])) {
                                echo '<em>' , $txt['tp-cannotfetchfile'] , '</em>';
                            }
                            else {
                                include($article['fileimport']);
                            }
                            $context['TPortal']['printbody'] = '';
                        }
                        elseif($article['type'] == 'bbc') {
                            $context['TPortal']['printbody'] = parse_bbc($what);
                        }
                        else {
                            $context['TPortal']['printbody'] = $what;
                        }

                        $context['TPortal']['print'] = '<a href="' .$scripturl . '?page='. $article['id'] . '"><strong>' . $txt['tp-printgoback'] . '</strong></a>';

                        loadtemplate('TPprint');
                        $context['template_layers'] = array('tp_print');
                        $context['sub_template'] = 'tp_print_body';
                        tp_hidebars();
                    }
                    // linktree?
                    if(!in_array('linktree', $context['TPortal']['article']['visual_options'])) {
                        $context['linktree'][0] = array('url' => '', 'name' => '');
                    }
                    else {
                        // we need the categories for the linktree
                        $allcats    = \TinyPortal\Model\Category::getInstance()->select(array('*') , array('item_type' => 'category'));

                        // setup the linkree
                        TPSubs::getInstance()->strip_linktree();

                        // do the category have any parents?
                        $parents = array();
                        $parent = $context['TPortal']['article']['category'];
                        if(count($allcats) > 0) {
                            while($parent !=0 && isset($allcats[$parent]['id'])) {
                                $parents[] = array(
                                    'id' => $allcats[$parent]['id'],
                                    'name' => $allcats[$parent]['display_name'],
                                    'shortname' => !empty($allcats[$parent]['short_name']) ? $allcats[$parent]['short_name'] : $allcats[$parent]['id'],
                                );
                                $parent = $allcats[$parent]['parent'];
                            }
                        }
                        // make the linktree
                        $parts = array_reverse($parents, TRUE);
                        // add to the linktree
                        foreach($parts as $parent) {
                            TPSubs::getInstance()->addLinkTree($scripturl.'?cat='. $parent['shortname'], $parent['name']);
                        }

                        TPSubs::getInstance()->addLinkTree($scripturl.'?page='. (!empty($context['TPortal']['article']['shortname']) ? $context['TPortal']['article']['shortname'] : $context['TPortal']['article']['id']), $context['TPortal']['article']['subject']);
                    }

                    $context['page_title'] = $context['TPortal']['article']['subject'];

                    if (defined('WIRELESS') && WIRELESS) {
                        $context['TPortal']['single_article'] = true;
                        loadtemplate('TPwireless');
                        // decide what subtemplate
                        $context['sub_template'] = WIRELESS_PROTOCOL . '_tp_page';
                    }

                }
                else {
                    $context['art_error'] = true;
                }

                if(allowedTo('tp_articles')) {
                    $now = time();
                    if((!empty($article['pub_start']) && $article['pub_start'] > $now) || (!empty($article['pub_end']) && $article['pub_end'] < $now)) {
                        $context['tportal']['article_expired'] = $article['id'];
                        $context['TPortal']['tperror'] = '<span class="error largetext">'. $txt['tp-expired-start']. '</span><p>' .standardTime($article['pub_start']). '' .$txt['tp-expired-start2']. '' . standardTime($article['pub_end']).'</p>';
                    }
                }
                return $article['id'];
            }
            else {
                $context['art_error'] = true;
            }
        }
        else {
            return;
        }

        return;

    }}}

    function action_category() {{{
        //return if not quite a category
        if((isset($_GET['area']) && $_GET['area'] == 'manageboards') || isset($_GET['action'])) {
            return;
        }

        global $context, $scripturl, $txt, $modSettings;

        \loadTemplate('TPortal');

        $db = TPDatabase::getInstance();
        $now = time();

        // check validity and fetch it
        if(!empty($_GET['cat'])) {
            $cat            = TPUtil::filter('cat', 'get', 'string');
            // get the category first
            if(is_numeric($cat)) {
                $category   = \TinyPortal\Model\Category::getInstance()->select(array('*') , array('id' => $cat));
            }
            else {
                $category   = \TinyPortal\Model\Category::getInstance()->select(array('*') , array('short_name' => $cat));
            }
            if(is_array($category) && (count($category) > 0)) {
                $category = $category[0];
                // check permission
                if(TPPermissions::getInstance()->getPermissions($category['access'], '')) {
                    // get the sorting from the category
                    $op = explode('|', $category['settings']);
                    $options = array();
                    foreach($op as $po => $val) {
                        $a = explode('=', $val);
                        if(isset($a[1])) {
                            $options[$a[0]] = $a[1];
                        }
                    }

                    $catsort    = isset($options['sort']) ? $options['sort'] : 'date';
                    if($catsort == 'authorID') {
                        $catsort = 'author_id';
                    }

                    $catsort_order  = isset($options['sortorder']) ? $options['sortorder'] : 'desc';
                    $max            = empty($options['articlecount']) ? $context['TPortal']['frontpage_limit'] : $options['articlecount'];
                    $start          = $context['TPortal']['mystart'];

                    // some swapping to avoid compability issues
                    $options['catlayout'] = isset($options['catlayout']) ? $options['catlayout'] : 1;

                    // make the template
                    if($options['catlayout'] == 7) {
                        $context['TPortal']['frontpage_template'] = $category['custom_template'];
                    }

                    // allowed and all is well, go on with it.
                    $context['TPortal']['category'] = $category;
                    $context['TPortal']['category']['articles'] = array();

                    // copy over the options as well
                    $context['TPortal']['category']['options'] = $options;

                    // set bars on/off according to options, setting override
                    $all = array('centerpanel', 'leftpanel', 'rightpanel', 'toppanel', 'bottompanel', 'lowerpanel');
                    for($p = 0; $p < 6; $p++) {
                        if(isset($options[$all[$p]]) && $context['TPortal'][$all[$p]] == 1) {
                            $context['TPortal'][$all[$p]] = 1;
                        }
                        else {
                            $context['TPortal'][$all[$p]] = 0;
                        }
                    }

                    // fallback value
                    if(!isset($context['TPortal']['category']['options']['catlayout'])) {
                        $context['TPortal']['category']['options']['catlayout'] = 1;
                    }

                    $request = $db->query('', '
                        SELECT art.id, ( CASE WHEN art.useintro = 1 THEN art.intro ELSE  art.body END ) AS body, mem.email_address AS email_address,
                            art.date, art.category, art.subject, art.author_id as author_id, art.frame, art.comments, art.options,
                            art.comments_var, art.views, art.rating, art.voters, art.shortname, art.useintro, art.intro,
                            art.fileimport, art.topic, art.locked, art.illustration, COALESCE(art.type, \'html\') AS rendertype , COALESCE(art.type, \'html\') AS type,
                            COALESCE(mem.real_name, art.author) as real_name, mem.avatar, mem.posts, mem.date_registered AS date_registered, mem.last_login AS last_login,
                            COALESCE(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type as attachment_type
                        FROM {db_prefix}tp_articles AS art
                        LEFT JOIN {db_prefix}members AS mem ON (art.author_id = mem.id_member)
                        LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member AND a.attachment_type != 3)
                        WHERE art.category = {int:cat}
                        AND ((art.pub_start = 0 AND art.pub_end = 0)
                        OR (art.pub_start !=0 AND art.pub_start < '.$now.' AND art.pub_end = 0)
                        OR (art.pub_start = 0 AND art.pub_end != 0 AND art.pub_end > '.$now.')
                        OR (art.pub_start != 0 AND art.pub_end != 0 AND art.pub_end > '.$now.' AND art.pub_start < '.$now.'))
                        AND art.off = 0
                        AND art.approved = 1
                        ORDER BY art.sticky desc, art.'.$catsort.' '.$catsort_order.'
                        LIMIT {int:start}, {int:max}',
                        array(
                            'cat' => $category['id'],
                            'start' => $start,
                            'max' => $max,
                        )
                    );

                    if($db->num_rows($request) > 0) {
                        $total = $db->num_rows($request);
                        $col1 = ceil($total / 2);
                        $counter = 0;
                        $context['TPortal']['category']['col1'] = array(); $context['TPortal']['category']['col2'] = array();
                        while($row = $db->fetch_assoc($request)) {
                            // Add the rating together
                            $row['rating'] = array_sum(explode(',', $row['rating']));
                            // expand the vislaoptions
                            $row['visual_options'] = explode(',', $row['options']);

                            $row['avatar'] = determineAvatar( array(
                                        'avatar'            => $row['avatar'],
                                        'emai_addressl'     => $row['email_address'],
                                        'filename'          => !empty($row['filename']) ? $row['filename'] : '',
                                        'id_attach'         => $row['id_attach'],
                                        'attachment_type'   => $row['attachment_type'],
                                    )
                            )['image'];

                            if($counter == 0) {
                                $context['TPortal']['category']['featured'] = $row;
                            }
                            elseif($counter < $col1 ) {
                                $context['TPortal']['category']['col1'][] = $row;
                            }
                            elseif($counter > $col1 || $counter == $col1) {
                                $context['TPortal']['category']['col2'][] = $row;
                            }
                            $counter++;
                        }
                        $db->free_result($request);
                    }

                    // any children then?
                    $allcats = array();
                    $context['TPortal']['category']['children'] = array();
                    $request =  $db->query('', '
                        SELECT cat.id, cat.display_name, cat.parent, COUNT(art.id) as articlecount
                        FROM {db_prefix}tp_categories AS cat
                        LEFT JOIN {db_prefix}tp_articles AS art ON (art.category = cat.id)
                        WHERE cat.item_type = {string:type} GROUP BY art.category, cat.id, cat.display_name, cat.parent',
                        array('type' => 'category')
                    );
                    if($db->num_rows($request) > 0) {
                        while($row = $db->fetch_assoc($request)) {
                            // get any children
                            if($row['parent'] == $cat) {
                                $context['TPortal']['category']['children'][] = $row;
                            }
                            $allcats[$row['id']] = $row;
                        }
                        $db->free_result($request);
                    }

                    $context['TPortal']['category']['no_articles'] = false;

                    // get how many articles in all
                    $request =  $db->query('', '
                        SELECT COUNT(*) FROM {db_prefix}tp_articles as art
                        WHERE art.category = {int:cat}
                        AND ((art.pub_start = 0 AND art.pub_end = 0)
                        OR (art.pub_start != 0 AND art.pub_start < '.$now.' AND art.pub_end = 0)
                        OR (art.pub_start = 0 AND art.pub_end != 0 AND art.pub_end > '.$now.')
                        OR (art.pub_start !=0 AND art.pub_end != 0 AND art.pub_end > '.$now.' AND art.pub_start < '.$now.'))
                        AND art.off = 0 AND art.approved = 1',
                        array('cat' => $category['id'])
                    );
                    if($db->num_rows($request)>0) {
                        $row = $db->fetch_row($request);
                        $all_articles = $row[0];
                    }
                    else {
                        $all_articles                                   = 0;
                    }

                    if($all_articles == 0) {
                        $context['TPortal']['category']['no_articles']  = true;
                    }

                    // make the pageindex!
                    $context['TPortal']['pageindex'] = TPSubs::getInstance()->pageIndex($scripturl . '?cat=' . $cat, $start, $all_articles, $max);

                    // setup the linkree
                    TPSubs::getInstance()->strip_linktree();

                    // do the category have any parents?
                    $parents = array();
                    $parent = $context['TPortal']['category']['parent'];
                    // save the immediate for wireless

                    if (defined('WIRELESS') && WIRELESS) {
                        if($context['TPortal']['category']['parent'] > 0) {
                            $context['TPortal']['category']['catname'] =  $allcats[$context['TPortal']['category']['parent']]['display_name'];
                        }
                        else {
                            $context['TPortal']['category']['catname'] =  $txt['tp-frontpage'];
                        }
                    }

                    while($parent != 0) {
                        $parents[] = array(
                            'id' => $allcats[$parent],
                            'name' => $allcats[$parent]['display_name'],
                            'shortname' => !empty($allcats[$parent]['short_name']) ? $allcats[$parent]['short_name'] : $allcats[$parent]['id'],
                        );
                        $parent = $allcats[$parent]['parent'];
                    }

                    // make the linktree
                    $parts = array_reverse($parents, TRUE);
                    // add to the linktree
                    foreach($parts as $parent) {
                        TPSubs::getInstance()->addLinkTree($scripturl.'?cat='. $parent['shortname'] , $parent['name']);
                    }

                    if(!empty($context['TPortal']['category']['shortname'])) {
                        TPSubs::getInstance()->addLinkTree($scripturl.'?cat='. $context['TPortal']['category']['short_name'], $context['TPortal']['category']['display_name']);
                    }
                    else {
                        TPSubs::getInstance()->addLinkTree($scripturl.'?cat='. $context['TPortal']['category']['id'], $context['TPortal']['category']['display_name']);
                    }

                    // check clist
                    $context['TPortal']['clist'] = array();
                    foreach(explode(',' , $context['TPortal']['cat_list']) as $cl => $value) {
                        if(isset($allcats[$value]) && is_numeric($value)) {
                            $context['TPortal']['clist'][] = array(
                                    'id' => $value,
                                    'name' => $allcats[$value]['display_name'],
                                    'selected' => $value == $cat ? true : false,
                                    );
                            $txt['catlist'. $value] = $allcats[$value]['display_name'];
                        }
                    }
                    $context['TPortal']['show_catlist'] = count($context['TPortal']['clist']) > 0 ? true : false;

                    $context['page_title'] = $context['TPortal']['category']['display_name'];
                    return $category['id'];
                }
                else {
                    return;
                }
            }
            else {
                $context['cat_error'] = true;
            }
        }
        else {
            return;
        }

    }}}

    function action_frontpage() {{{
        global $context, $scripturl, $user_info, $modSettings, $txt;

        \loadTemplate('TPortal');

        $db = TPDatabase::getInstance();

        // check we aren't in any other section because 'cat' is used in ELK and TP
        if(isset($_GET['action']) || isset($_GET['board']) || isset($_GET['topic'])) {
            return;
        }

        $now = time();
        // set up visual options for frontpage
        $context['TPortal']['visual_opts'] = explode(',', $context['TPortal']['frontpage_visual']);

        // first, the panels
        foreach(array('left', 'right', 'center', 'top', 'bottom', 'lower') as $pan => $panel) {
            if($context['TPortal'][$panel.'panel'] == 1 && in_array($panel, $context['TPortal']['visual_opts'])) {
                $context['TPortal'][$panel.'panel'] = 1;
            }
            else {
                $context['TPortal'][$panel.'panel'] = 0;
            }
        }
        // get the sorting
        foreach($context['TPortal']['visual_opts'] as $vi => $vo) {
            if(substr($vo, 0, 5) == 'sort_') {
                $catsort = substr($vo, 5);
            }
            else {
                $catsort = 'date';
            }

            if(substr($vo, 0, 10) == 'sortorder_') {
                $catsort_order = substr($vo, 10);
            }
            else {
                $catsort_order = 'desc';
            }
        }

        if(!in_array($catsort, array('date', 'author_id', 'id', 'parse'))) {
            $catsort = 'date';
        }

        $max    = $context['TPortal']['frontpage_limit'];
        $start  = $context['TPortal']['mystart'];

        // fetch the articles, sorted
        switch($context['TPortal']['front_type']) {
            // Only articles
            case 'articles_only':
                // first, get all available
                $artgroups = '';
                if(!$context['user']['is_admin']) {
                    $artgroups = TPUtil::find_in_set($user_info['groups'], 'var.access', 'AND');
                }


                $tpArticle          = TPArticle::getInstance();
                $articles_total     = $tpArticle->getTotalArticles($artgroups);
                // make the pageindex!
                $context['TPortal']['pageindex'] = TPSubs::getInstance()->pageIndex($scripturl .'?frontpage', $start, $articles_total, $max);

                $request =  $db->query('', '
                    SELECT art.id, ( CASE WHEN art.useintro = 1 THEN art.intro ELSE  art.body END ) AS body,
                        art.date, art.category, art.subject, art.author_id as author_id, var.display_name as category_name, var.short_name as category_shortname,
                        art.frame, art.comments, art.options, art.intro, art.useintro,
                        art.comments_var, art.views, art.rating, art.voters, art.shortname,
                        art.fileimport, art.topic, art.locked, art.illustration,art.type as rendertype ,
                        COALESCE(mem.real_name, art.author) as real_name, mem.avatar, mem.posts, mem.date_registered as date_registered,mem.last_login as last_login,
                        COALESCE(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type as attachment_type, mem.email_address AS email_address
                    FROM {db_prefix}tp_articles AS art
                    LEFT JOIN {db_prefix}tp_categories AS var ON(var.id = art.category)
                    LEFT JOIN {db_prefix}members AS mem ON (art.author_id = mem.id_member)
                    LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member AND a.attachment_type!=3)
                    WHERE art.off = 0
                    ' . $artgroups . '
                    AND ((art.pub_start = 0 AND art.pub_end = 0)
                    OR (art.pub_start != 0 AND art.pub_start < '.$now.' AND art.pub_end = 0)
                    OR (art.pub_start = 0 AND art.pub_end != 0 AND art.pub_end > '.$now.')
                    OR (art.pub_start != 0 AND art.pub_end != 0 AND art.pub_end > '.$now.' AND art.pub_start < '.$now.'))
                    AND art.category > 0
                    AND art.approved = 1
                    AND (art.frontpage = 1 OR art.featured = 1)
                    ORDER BY art.featured DESC, art.sticky DESC, art.'.$catsort.' '. $catsort_order .'
                    LIMIT {int:start}, {int:max}',
                    array('start' => $start, 'max' => $max)
                );
                if($db->num_rows($request) > 0) {
                    $total = $db->num_rows($request);
                    $col1 = ceil($total / 2);
                    $col2 = $total - $col1;
                    $counter = 0;

                    $context['TPortal']['category'] = array(
                        'articles' => array(),
                        'col1' => array(),
                        'col2' => array(),
                        'options' => array(
                            'catlayout' => $context['TPortal']['frontpage_catlayout'],
                            'layout' => $context['TPortal']['frontpage_layout'],
                        )
                    );

                    while($row = $db->fetch_assoc($request)) {
                        // expand the vislaoptions
                        $row['visual_options'] = explode(',', $row['options']);

                        $row['avatar'] = determineAvatar( array(
                                    'avatar'            => $row['avatar'],
                                    'email_address'     => $row['email_address'],
                                    'filename'          => !empty($row['filename']) ? $row['filename'] : '',
                                    'id_attach'         => $row['id_attach'],
                                    'attachment_type'   => $row['attachment_type'],
                                )
                        )['image'];

                        if($counter == 0) {
                            $context['TPortal']['category']['featured'] = $row;
                        }
                        elseif($counter < $col1 ) {
                            $context['TPortal']['category']['col1'][] = $row;
                        }
                        elseif($counter > $col1 || $counter == $col1) {
                            $context['TPortal']['category']['col2'][] = $row;
                        }
                        $counter++;
                    }
                    $db->free_result($request);
                }
            break;
        case 'single_page':
            $request =  $db->query('', '
                SELECT art.id, ( CASE WHEN art.useintro = 1 THEN art.intro ELSE  art.body END ) AS body,
                    art.date, art.category, art.subject, art.author_id as author_id, var.display_name as category_name, var.short_name as category_shortname,
                    art.frame, art.comments, art.options, art.intro, art.useintro,
                    art.comments_var, art.views, art.rating, art.voters, art.shortname,
                    art.fileimport, art.topic, art.locked, art.illustration,art.type as rendertype ,
                    COALESCE(mem.real_name, art.author) as real_name, mem.avatar, mem.posts, mem.date_registered as date_registered,mem.last_login as last_login,
                    COALESCE(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type as attachment_type, mem.email_address AS email_address
                FROM {db_prefix}tp_articles AS art
                LEFT JOIN {db_prefix}tp_categories AS var ON(var.id = art.category)
                LEFT JOIN {db_prefix}members AS mem ON (art.author_id = mem.id_member)
                LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member AND a.attachment_type!=3)
                WHERE art.off = 0
                AND ((art.pub_start = 0 AND art.pub_end = 0)
                OR (art.pub_start != 0 AND art.pub_start < '.$now.' AND art.pub_end = 0)
                OR (art.pub_start = 0 AND art.pub_end != 0 AND art.pub_end > '.$now.')
                OR (art.pub_start != 0 AND art.pub_end != 0 AND art.pub_end > '.$now.' AND art.pub_start < '.$now.'))
                AND art.featured = 1
                AND art.approved = 1
                LIMIT 1'
            );
            if($db->num_rows($request) > 0) {
                $context['TPortal']['category'] = array(
                    'articles' => array(),
                    'col1' => array(),
                    'col2' => array(),
                    'options' => array(
                        'catlayout' => $context['TPortal']['frontpage_catlayout'],
                        'layout' => $context['TPortal']['frontpage_layout'],
                    )
                );

                $row = $db->fetch_assoc($request);
                // expand the vislaoptions
                $row['visual_options'] = explode(',', $row['options']);

                $row['avatar'] = determineAvatar( array(
                            'avatar'            => $row['avatar'],
                            'email_address'     => $row['email_address'],
                            'filename'          => !empty($row['filename']) ? $row['filename'] : '',
                            'id_attach'         => $row['id_attach'],
                            'attachment_type'   => $row['attachment_type'],
                        )
                )['image'];

                $context['TPortal']['category']['featured'] = $row;
                $db->free_result($request);
            }
            break;
        // Only forum-topics
        case 'forum_only':
        // Promoted topics only
        case 'forum_selected':
            $totalmax = 200;

            TPSubs::getInstance()->loadLanguage('Stats');

            // Find the post ids.
            if($context['TPortal']['front_type'] == 'forum_only') {
                $request =  $db->query('', '
                    SELECT t.id_first_msg AS id_first_message
                    FROM {db_prefix}topics AS t
                    INNER JOIN {db_prefix}boards AS b
                        ON t.id_board = b.id_board
                    WHERE t.id_board IN({raw:board})
                    ' . ($context['TPortal']['allow_guestnews'] == 0 ? 'AND {query_see_board}' : '') . '
                    ORDER BY t.id_first_msg DESC
                    LIMIT {int:max} OFFSET {int:offset}',
                    array(
                        'board'     => $context['TPortal']['SSI_board'],
                        'max'       => $totalmax,
                        'offset'    => $start,
                    )
                );
            }
            else {
                $request =  $db->query('', '
                    SELECT t.id_first_msg AS id_first_message
                    FROM {db_prefix}topics AS t
                    INNER JOIN {db_prefix}boards AS b
                        ON t.id_board = b.id_board
                    WHERE t.id_topic IN(' . (empty($context['TPortal']['frontpage_topics']) ? 0 : '{raw:topics}') .')
                    ' . ($context['TPortal']['allow_guestnews'] == 0 ? 'AND {query_see_board}' : '') . '
                    ORDER BY t.id_first_msg DESC
                    LIMIT {int:max} OFFSET {int:offset}',
                    array(
                        'topics'    => $context['TPortal']['frontpage_topics'],
                        'max'       => $totalmax,
                        'offset'    => $start,
                    )
                );
            }

            $posts = array();
            while ($row = $db->fetch_assoc($request)) {
                $posts[] = $row['id_first_message'];
            }
            $db->free_result($request);

            if (empty($posts)) {
                return array();
            }

            $tpArticle  = TPArticle::getInstance();
            $posts      = $tpArticle->getForumPosts($posts);

            // make the pageindex!
            $context['TPortal']['pageindex'] = TPSubs::getInstance()->pageIndex($scripturl .'?frontpage', $start, $start + count($posts), $max);

            if(count($posts) > 0) {
                $total      = min(count($posts), $max);
                $col1       = ceil($total / 2);
                $col2       = $total - $col1;
                $counter    = 0;

                $context['TPortal']['category'] = array(
                    'articles' => array(),
                    'col1' => array(),
                    'col2' => array(),
                    'options' => array(
                        'catlayout' => $context['TPortal']['frontpage_catlayout'],
                        'layout' => $context['TPortal']['frontpage_layout'],
                    )
                );

                foreach($posts as $row) {
                    if($counter >= $max) {
                        break;
                    }
                    if($counter == 0) {
                        $context['TPortal']['category']['featured'] = $row;
                    }
                    elseif($counter < $col1 && $counter > 0) {
                        $context['TPortal']['category']['col1'][] = $row;
                    }
                    elseif($counter > $col1 || $counter == $col1) {
                        $context['TPortal']['category']['col2'][] = $row;
                    }
                    $counter++;
                }
            }
            break;
        // Forum-topics and articles - sorted on date
        case 'forum_articles':
        // Promoted topics + articles - sorted on date
        case 'forum_selected_articles':
            // first, get all available
            $artgroups = '';
            if(!$context['user']['is_admin']) {
                $artgroups = TPUtil::find_in_set($user_info['groups'], 'var.access', 'AND');
            }

            $totalmax = 200;
            TPSubs::getInstance()->loadLanguage('Stats');
            $year = 10000000;
            $year2 = 100000000;

            $request =  $db->query('',
            'SELECT art.id, art.date, art.sticky, art.featured
                FROM {db_prefix}tp_articles AS art
                INNER JOIN {db_prefix}tp_categories AS var
                    ON var.id = art.category
                WHERE art.off = 0
                ' . $artgroups . '
                AND ((art.pub_start = 0 AND art.pub_end = 0)
                OR (art.pub_start != 0 AND art.pub_start < '.$now.' AND art.pub_end = 0)
                OR (art.pub_start = 0 AND art.pub_end != 0 AND art.pub_end > '.$now.')
                OR (art.pub_start != 0 AND art.pub_end != 0 AND art.pub_end > '.$now.' AND art.pub_start < '.$now.'))
                AND art.category > 0
                AND art. approved = 1
                AND (art.frontpage = 1 OR art. featured = 1)
                ORDER BY art.featured DESC, art.sticky desc, art.date DESC'
            );

            $posts = array();
            if($db->num_rows($request) > 0) {
                while ($row = $db->fetch_assoc($request)) {
                    if($row['sticky'] == 1) {
                        $row['date'] += $year;
                    }
                    if($row['featured'] == 1) {
                        $row['date'] += $year2;
                    }
                    $posts[$row['date'].'_' . sprintf("%06s", $row['id'])] = 'a_' . $row['id'];
                }
                $db->free_result($request);
            }

            // Find the post ids.
            if($context['TPortal']['front_type'] == 'forum_articles') {
                $request =  $db->query('', '
                    SELECT t.id_first_msg AS id_first_msg , m.poster_time AS date
                    FROM {db_prefix}topics AS t
                    INNER JOIN {db_prefix}boards AS b
                        ON t.id_board = b.id_board
                    INNER JOIN {db_prefix}messages AS m
                        ON t.id_first_msg = m.id_msg
                    WHERE t.id_board IN({raw:board})
                    ' . ($context['TPortal']['allow_guestnews'] == 0 ? 'AND {query_see_board}' : '') . '
                    ORDER BY date DESC
                    LIMIT {int:max}',
                    array(
                        'board' => $context['TPortal']['SSI_board'],
                        'max' => $totalmax)
                );
            }
            else {
                $request =  $db->query('', '
                    SELECT t.id_first_msg AS id_first_msg , m.poster_time AS date
                    FROM {db_prefix}topics AS t
                    INNER JOIN {db_prefix}boards AS b
                        ON t.id_board = b.id_board
                    INNER JOIN {db_prefix}messages AS m
                        ON t.id_first_msg = m.id_msg
                    WHERE t.id_topic IN(' . (empty($context['TPortal']['frontpage_topics']) ? '0' : $context['TPortal']['frontpage_topics']) .')
                    ' . ($context['TPortal']['allow_guestnews'] == 0 ? 'AND {query_see_board}' : '') . '
                    ORDER BY date DESC'
                );
            }

            if($db->num_rows($request) > 0) {
                while ($row = $db->fetch_assoc($request)) {
                    $posts[$row['date'].'_' . sprintf("%06s", $row['id_first_msg'])] = 'm_' . $row['id_first_msg'];
                }
                $db->free_result($request);
            }

            // Sort the articles/posts before grabing the limit, otherwise they are out of order
            ksort($posts, SORT_NUMERIC);
            $posts = array_reverse($posts);

            // which should we select
            $aposts = array();
            $mposts = array();
            $a = 0;
            foreach($posts as $ab => $val) {
                if(($a == $start || $a > $start) && $a < ($start + $max)) {
                    if(substr($val, 0, 2) == 'a_') {
                        $aposts[] = substr($val, 2);
                    }
                    elseif(substr($val, 0, 2) == 'm_') {
                        $mposts[] = substr($val, 2);
                    }
                }
                $a++;
            }

            $thumbs = array();
            if(count($mposts) > 0) {
                // Find the thumbs.
                $request =  $db->query('', '
                    SELECT id_thumb FROM {db_prefix}attachments
                    WHERE id_msg IN ({array_int:posts})
                    AND id_thumb > 0',
                    array('posts' => $mposts)
                );

                if($db->num_rows($request) > 0) {
                    while ($row = $db->fetch_assoc($request)) {
                        $thumbs[] = $row['id_thumb'];
                    }
                    $db->free_result($request);
                }
            }
            // make the pageindex!
            $context['TPortal']['pageindex'] = TPSubs::getInstance()->pageIndex($scripturl .'?frontpage', $start, count($posts), $max);

            // Clear request so that the check further down works correctly
            $request = false;

            $context['TPortal']['category'] = array(
                'articles' => array(),
                'col1' => array(),
                'col2' => array(),
                'options' => array(
                    'catlayout' => $context['TPortal']['frontpage_catlayout'],
                    'layout' => $context['TPortal']['frontpage_layout'],
                ),
                'category_opts' => array(
                    'catlayout' => $context['TPortal']['frontpage_catlayout'],
                    'template' => $context['TPortal']['frontpage_template'],
                )
            );

            $tpArticle  = TPArticle::getInstance();
            $posts      = $tpArticle->getForumPosts($mposts);

            // next up is articles
            if(count($aposts) > 0) {
                $articles   = $tpArticle->getArticle($aposts);
                foreach ( $articles as $k => $row ) {
                    // expand the vislaoptions
                    $row['visual_options'] = explode(',', $row['options']);
                    $row['visual_options']['layout'] = $context['TPortal']['frontpage_layout'];
                    $row['rating'] = array_sum(explode(',', $row['rating']));
                    $row['avatar'] = determineAvatar( array(
                                'avatar'            => $row['avatar'],
                                'email_address'     => $row['email_address'],
                                'filename'          => !empty($row['filename']) ? $row['filename'] : '',
                                'id_attach'         => $row['id_attach'],
                                'attachment_type'   => $row['attachment_type'],
                            )
                    )['image'];
                    // we need some trick to put featured/sticky on top
                    $sortdate = $row['date'];
                    if($row['sticky'] == 1) {
                        $sortdate = $row['date'] + $year;
                    }
                    if($row['featured'] == 1) {
                        $sortdate = $row['date'] + $year2;
                    }
                    $posts[$sortdate.'0' . sprintf("%06s", $row['id'])] = $row;
                }
                unset($tpArticle);
            }
            $total      = count($posts);
            $col1       = ceil($total / 2);
            $col2       = $total - $col1;
            $counter    = 0;

            // divide it
            ksort($posts,SORT_NUMERIC);
            $all = array_reverse($posts);

            foreach($all as $p => $row) {
                if($counter == 0) {
                    $context['TPortal']['category']['featured'] = $row;
                }
                else if($counter < $col1 && $counter > 0) {
                    $context['TPortal']['category']['col1'][] = $row;
                }
                else if($counter > $col1 || $counter == $col1) {
                    $context['TPortal']['category']['col2'][] = $row;
                }
                $counter++;
            }
            break;
        }

        // collect up frontblocks
        $blocks = array('front' => array());

        // set the membergroup access
        $access = TPUtil::find_in_set($user_info['groups'], 'access');

        if(allowedTo('tp_blocks') && (!empty($context['TPortal']['admin_showblocks']) || !isset($context['TPortal']['admin_showblocks']))) {
            $access = '1=1';
        }

        $display = '';
        if(!empty($context['TPortal']['uselangoption'])) {
            $display = TPUtil::find_in_set(array('tlang='.$user_info['language']), 'display');
            if(isset($display)) {
                $display = ' AND '. $display;
            }
        }

        // get the blocks
        $request =  $db->query('', '
            SELECT * FROM {db_prefix}tp_blocks
            WHERE off = 0
            AND bar = 4
            AND '. $access .'
            '.$display. '
            ORDER BY pos,id ASC'
        );

        $count = array('front' => 0);
        $fetch_articles = array();
        $fetch_article_titles = array();
        $panels = array(4 => 'front');

        $tpBlock = new TPBlock();

        if ($db->num_rows($request) > 0) {
            while($row = $db->fetch_assoc($request)) {

                // decode the block settings
                $set = json_decode($row['settings'], true);

                // some tests to minimize sql calls
                if($row['type'] == 7) {
                    $test_themebox = true;
                }
                elseif($row['type'] == 18) {
                    $test_articlebox = true;
                    if(is_numeric($row['body'])) {
                        $fetch_articles[]=$row['body'];
                    }
                }
                elseif($row['type'] == 19) {
                    $test_catbox = true;
                    if(is_numeric($row['body'])) {
                        $fetch_article_titles[] = $row['body'];
                    }
                }
                elseif($row['type'] == 20) {
                    call_integration_hook('integrate_tp_blocks', array(&$row));
                }
                $can_edit   = TPPermissions::getInstance()->getPermissions($row['editgroups'], '');
                $can_manage = allowedTo('tp_blocks');
                if($can_manage) {
                    $can_edit = false;
                }

                $blocks[$panels[$row['bar']]][$count[$panels[$row['bar']]]] = array(
                    'frame' => $row['frame'],
                    'title' => strip_tags($row['title'], '<center>'),
                    'type' => $tpBlock->getBlockType($row['type']),
                    'body' => $row['body'],
                    'visible' => $row['visible'],
                    'var1' => $set['var1'],
                    'var2' => $set['var2'],
                    'var3' => $set['var3'],
                    'var4' => $set['var4'],
                    'var5' => $set['var5'],
                    'id' => $row['id'],
                    'lang' => $row['lang'],
                    'display' => $row['display'],
                    'can_edit' => $can_edit,
                    'can_manage' => $can_manage,
                );

                $count[$panels[$row['bar']]]++;
            }
            $db->free_result($request);
        }

        if(count($fetch_articles) > 0) {
            $fetchart = '(art.id='. implode(' OR art.id=', $fetch_articles).')';
        }
        else {
            $fetchart='';
        }

        if(count($fetch_article_titles) > 0) {
            $fetchtitles= '(art.category='. implode(' OR art.category=', $fetch_article_titles).')';
        }
        else {
            $fetchtitles='';
        }

        // if a block displays an article
        if(isset($test_articlebox) && $fetchart != '') {
            $context['TPortal']['blockarticles'] = array();
            $request =  $db->query('', '
                SELECT art.*, var.display_name, var.parent, var.access, var.dt_log, var.page, var.settings, var.short_name, art.type as rendertype,
                    COALESCE(mem.real_name,art.author) as real_name, mem.avatar, mem.posts, mem.date_registered as date_registered, mem.last_login as last_login,
                    COALESCE(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type as attachment_type, var.custom_template, mem.email_address AS email_address
                FROM {db_prefix}tp_articles as art
                LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = art.author_id)
                LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = art.author_id AND a.attachment_type !=3)
                LEFT JOIN {db_prefix}tp_categories as var ON (var.id= art.category)
                WHERE ' . $fetchart.'
                AND art.off = 0
                AND ((art.pub_start = 0 AND art.pub_end = 0)
                OR (art.pub_start != 0 AND art.pub_start < '.$now.' AND art.pub_end = 0)
                OR (art.pub_start = 0 AND art.pub_end != 0 AND art.pub_end > '.$now.')
                OR (art.pub_start != 0 AND art.pub_end != 0 AND art.pub_end > '.$now.' AND art.pub_start < '.$now.'))
                AND art.category > 0
                AND art.approved = 1
                AND art.category > 0 AND art.category < 9999'
            );
            if($db->num_rows($request) > 0) {
                while($article = $db->fetch_assoc($request)) {
                    // allowed and all is well, go on with it.
                    $context['TPortal']['blockarticles'][$article['id']] = $article;
                    $context['TPortal']['blockarticles'][$article['id']]['avatar'] = determineAvatar( array(
                                'avatar'            => isset($row['avatar']) ? $row['avatar'] : '',
                                'email_address'     => isset($row['email_address']) ? $row['email_address'] : '',
                                'filename'          => !empty($row['filename']) ? $row['filename'] : '',
                                'id_attach'         => isset($row['id_attach']) ? $row['id_attach'] : '',
                                'attachment_type'   => isset($row['attachment_type']) ? $row['attachment_type'] : '',
                            )
                    )['image'];

                    // sort out the options
                    $context['TPortal']['blockarticles'][$article['id']]['visual_options'] = array();
                    // since these are inside blocks, some stuff has to be left out
                    $context['TPortal']['blockarticles'][$article['id']]['frame'] = 'none';
                }
                $db->free_result($request);
            }
        }

       // any cat listings from blocks?
        if(isset($test_catbox) && $fetchtitles != '') {
            $request =  $db->query('', '
                SELECT art.id, art.subject, art.date, art.category, art.author_id as author_id, art.shortname,
                COALESCE(mem.real_name,art.author) as real_name FROM {db_prefix}tp_articles AS art
                LEFT JOIN {db_prefix}members AS mem ON (art.author_id = mem.id_member)
                WHERE ' . 	$fetchtitles . '
                AND ((art.pub_start = 0 AND art.pub_end = 0)
                OR (art.pub_start != 0 AND art.pub_start < '.$now.' AND art.pub_end = 0)
                OR (art.pub_start = 0 AND art.pub_end != 0 AND art.pub_end > '.$now.')
                OR (art.pub_start != 0 AND art.pub_end != 0 AND art.pub_end > '.$now.' AND art.pub_start < '.$now.'))
                AND art.off = 0
                AND art.category > 0
                AND art.approved = 1'
            );

            if (!isset($context['TPortal']['blockarticle_titles'])) {
                $context['TPortal']['blockarticle_titles'] = array();
            }

            if ($db->num_rows($request) > 0) {
                while($row = $db->fetch_assoc($request)) {
                    $context['TPortal']['blockarticle_titles'][$row['category']][$row['date'].'_'.$row['id']] = array(
                        'id' => $row['id'],
                        'subject' => $row['subject'],
                        'shortname' => $row['shortname'] != '' ? $row['shortname'] : $row['id'] ,
                        'category' => $row['category'],
                        'poster' => '<a href="'.$scripturl.'?action=profile;u='.$row['author_id'].'">'.$row['real_name'].'</a>',
                    );
                }
                $db->free_result($request);
            }
        }

        // check the panels
        foreach($panels as $p => $panel) {
            // any blocks at all?
            if($count[$panel] < 1)
                $context['TPortal'][$panel.'panel'] = 0;

        }

        $context['TPortal']['frontblocks'] = $blocks;

        if (defined('WIRELESS') && WIRELESS) {
            $context['TPortal']['single_article'] = false;
            loadtemplate('TPwireless');
            // decide what subtemplate
            $context['sub_template'] = WIRELESS_PROTOCOL . '_tp_frontpage';
        }
    }}}

    function action_credits() {{{
        global $context;

        tp_hidebars();
        $context['TPortal']['not_forum'] = false;

        if(TPSubs::getInstance()->loadLanguage('TPhelp') == false) {
            TPSubs::getInstance()->loadLanguage('TPhelp', 'english');
        }

        \loadTemplate('TPhelp');

    }}}

    function action_upshrink() {{{
        global $settings;

        if(isset($_GET['id']) && isset($_GET['state'])) {
            $blockid    = TPUtil::filter('id', 'get', 'string');
            $state      = TPUtil::filter('state', 'get', 'string');
            if(isset($_COOKIE['tp-upshrinks'])) {
                $shrinks = explode(',', $_COOKIE['tp-upshrinks']);
                if($state == 0 && !in_array($blockid, $shrinks)) {
                    $shrinks[] = $blockid;
                }
                elseif($state == 1 && in_array($blockid, $shrinks)) {
                    $spos = array_search($blockid, $shrinks);
                    if($spos > -1) {
                        unset($shrinks[$spos]);
                    }
                }
                $newshrink = implode(',', $shrinks);
                setcookie ('tp-upshrinks', $newshrink , time()+7776000);
            }
            else {
                if($state == 0) {
                    setcookie ('tp-upshrinks', $blockid, (time()+7776000));
                }
            }
            // Don't output anything...
            //$tid = time();
            //redirectexit($settings['images_url'] . '/blank.png?ti='.$tid);
            redirectexit();
        }
        else {
            redirectexit();
        }

    }}}

}

?>
