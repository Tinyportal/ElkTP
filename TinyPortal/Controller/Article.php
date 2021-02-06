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
use \TinyPortal\Model\Mentions as TPMentions;
use \TinyPortal\Model\Subs as TPSubs;
use \TinyPortal\Model\Util as TPUtil;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Article extends \Action_Controller
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
        TPSubs::getInstance()->strip_linktree();

        require_once(SUBSDIR . '/Action.class.php');
        $subActions = array (
            'showcomments'      => array($this, 'action_show_comments', array()),
            'comment'           => array($this, 'action_insert_comment', array()),
            'killcomment'       => array($this, 'action_delete_comment', array()),
            'editcomment'       => array($this, 'action_edit_comment', array()),
            'rate_article'      => array($this, 'action_rate', array()),
            'myarticles'        => array($this, 'action_show', array()),
        );

        $sa = TPUtil::filter('sa', 'get', 'string');

        $action     = new \Action();
        $subAction  = $action->initialize($subActions, $sa);
        $action->dispatch($subAction);

    }}}

	function action_insert_comment() {{{

		global $user_info, $context, $txt;

		// check the session
		checkSession('post');

		if (!allowedTo('tp_artcomment')) {
			throw new Elk_Exception($txt['tp-nocomments'], 'general');
		}

		$commenter  = $context['user']['id'];
		$article    = TPUtil::filter('tp_article_id', 'post', 'int');
		$title      = TPUtil::filter('tp_article_comment_title', 'post', 'string');
		$comment    = substr(TPUtil::htmlspecialchars($_POST['tp_article_bodytext']), 0, 65536);
		if(!empty($context['TPortal']['allow_links_article_comments'])==0 && TPUtil::hasLinks($comment)) {
			redirectexit('page='.$article.'#tp-comment');
		}

		require_once(SUBSDIR.'/Post.subs.php');
		preparsecode($comment);

		$tpArticle  = TPArticle::getInstance();
		$comment_id = $tpArticle->insertArticleComment($commenter, $article, $comment, $title);
		if($comment_id > 0)  {
			$mention_data['id']             = $article;
			$mention_data['content']        = $comment;
			$mention_data['type']           = 'tp_comment';
			$mention_data['member_id']      = $user_info['id'];
			$mention_data['username']       = $user_info['username'];
			$mention_data['action']         = 'mention';
			$mention_data['event_title']    = 'Article Mention';
			$mention_data['text']           = 'Article';

			$tpMention = TPMentions::getInstance();
			$tpMention->addMention($mention_data);

		}

		// go back to the article
		redirectexit('page='.$article.'#tp-comment');

	}}}

	function action_show_comments() {{{
		global $scripturl, $user_info, $txt, $context;

		$db = TPDatabase::getInstance();

		if(!empty($_GET['tpstart']) && is_numeric($_GET['tpstart'])) {
			$tpstart = $_GET['tpstart'];
		}
		else {
			$tpstart = 0;
		}

		$mylast = 0;
		$mylast = $user_info['last_login'];
		$showall = false;
		if(isset($_GET['showall'])) {
			$showall = true;
		}

		$request = $db->query('', '
			SELECT COUNT(var.display_name)
			FROM ({db_prefix}tp_categories AS var, {db_prefix}tp_articles AS art)
			WHERE var.type = {string:type}
			' . ((!$showall || $mylast == 0) ? 'AND var.dt_log > '.$mylast : '') .'
			AND art.id = var.page',
			array('type' => 'article_comment')
		);
		$check = $db->fetch_row($request);
		$db->free_result($request);

		$request = $db->query('', '
			SELECT art.subject, memb.real_name AS author, art.author_id AS authorID, var.display_name, var.parent, var.access,
			var.page, var.dt_log, mem.real_name AS realName,
			' . ($user_info['is_guest'] ? '1' : '(COALESCE(log.item, 0) >= var.dt_log)') . ' AS isRead
			FROM ({db_prefix}tp_categories AS var, {db_prefix}tp_articles AS art)
			LEFT JOIN {db_prefix}members AS memb ON (art.author_id = memb.id_member)
			LEFT JOIN {db_prefix}members AS mem ON (var.access = mem.id_member)
			LEFT JOIN {db_prefix}tp_data AS log ON (log.value = art.id AND log.type = 1 AND log.id_member = '.$context['user']['id'].')
			WHERE var.type = {string:type}
			AND art.id = var.page
			' . ((!$showall || $mylast == 0 ) ? 'AND var.dt_log > {int:last}' : '') .'
			ORDER BY var.dt_log DESC LIMIT {int:start}, 15',
			array('type' => 'article_comment', 'last' => $mylast, 'start' => $tpstart)
		);

		$context['TPortal']['artcomments']['new'] = array();

		if($db->num_rows($request) > 0) {
			while($row=$db->fetch_assoc($request)) {
				$context['TPortal']['artcomments']['new'][] = array(
					'page' => $row['page'],
					'subject' => $row['subject'],
					'title' => $row['display_name'],
					'comment' => $row['parent'],
					'membername' => $row['realName'],
					'time' => standardTime($row['dt_log']),
					'author' => $row['author'],
					'authorID' => $row['authorID'],
					'member_id' => $row['access'],
					'is_read' => $row['isRead'],
					'replies' => $check[0],
				);
			}
			$db->free_result($request);
		}

		// construct the pages
		$context['TPortal']['pageindex']        = TPSubs::getInstance()->pageIndex($scripturl.'?action=tparticle;sa=showcomments', $tpstart, $check[0], 15);
		$context['TPortal']['unreadcomments']   = true;
		$context['TPortal']['showall']          = $showall;
		TPadd_linktree($scripturl.'?action=tparticle;sa=showcomments' . ($showall ? ';showall' : '')  , $txt['tp-showcomments']);
		loadTemplate('TParticle');
		$context['sub_template'] = 'showcomments';
		if(loadLanguage('TParticle') == false) {
			loadLanguage('TParticle', 'english');
		};

	}}}

	function action_delete_comment() {{{

		global $context, $txt;

		if (!allowedTo('tp_artcomment')) {
			throw new Elk_Exception($txt['tp-nocomments'], 'general');
		}

		// edit or deleting a comment?
		if($context['user']['is_logged']) {
			// check that you indeed can edit or delete
			$comment = TPUtil::filter('comment', 'get', 'int');
			if(!is_numeric($comment)) {
				throw new Elk_Exception($txt['tp-noadmincomments'], 'general');
			}

			$tpArticle  = TPArticle::getInstance();
			$comment    = $tpArticle->getArticleComment($comment);
			if(is_array($comment)) {
				$tpArticle->deleteArticleComment($comment['id']);
				redirectexit('page='.$comment['item_id']);
			}
		}

	}}}

	function action_edit_comment() {{{
		global $context, $txt;

	   if (!allowedTo('tp_artcomment')) {
			throw new Elk_Exception($txt['tp-nocomments'], 'general');
		}

		if($context['user']['is_logged']) {
			// check that you indeed can edit or delete
			$comment = substr($_GET['sa'], 11);
			if(!is_numeric($comment)) {
				throw new Elk_Exception($txt['tp-noadmincomments'], 'general');
			}

			$tpArticle  = TPArticle::getInstance();
			$comment    = $tpArticle->getArticleComment($comment);
			if(is_array($comment)) {
				if(allowedTo('tp_articles') || $comment['member_id'] == $context['user']['id']) {
					$context['TPortal']['comment_edit'] = array(
						'id' => $row['id'],
						'title' => $row['display_name'],
						'body' => $row['parent'],
					);
					$context['sub_template'] = 'editcomment';
					loadTemplate('TParticle');
					if(loadLanguage('TParticle') == false) {
						loadLanguage('TParticle', 'english');
					};
				}
				throw new Elk_Exception($txt['tp-notallowed'], 'general');
			}
		}

	}}}

	function action_rate() {{{
		global $context;

		$db = TPDatabase::getInstance();
		// rating is underway
		if(isset($_POST['tp_article_rating_submit']) && $_POST['tp_article_type'] == 'article_rating') {
			// check the session
			checkSession('post');

			$commenter = $context['user']['id'];
			$article = $_POST['tp_article_id'];
			// check if the article indeed exists
			$request = $db->query('', '
				SELECT rating, voters FROM {db_prefix}tp_articles
				WHERE id = {int:artid}',
				array('artid' => $article)
			);
			if($db->num_rows($request) > 0) {
				$row = $db->fetch_row($request);
				$db->free_result($request);

				$voters = array();
				$ratings = array();
				$voters = explode(',', $row[1]);
				$ratings = explode(',', $row[0]);
				// check if we haven't rated anyway
				if(!in_array($context['user']['id'], $voters)) {
					if($row[0] != '') {
						$new_voters     = $row[1].','.$context['user']['id'];
						$new_ratings    = $row[0].','.$_POST['tp_article_rating'];
					}
					else {
						$new_voters     = $context['user']['id'];
						$new_ratings    = $_POST['tp_article_rating'];
					}
					// update ratings and raters
					$db->query('', '
						UPDATE {db_prefix}tp_articles
						SET rating = {string:rate} WHERE id = {int:artid}',
						array('rate' => $new_ratings, 'artid' => $article)
					);
					$db->query('', '
						UPDATE {db_prefix}tp_articles
						SET voters = {string:vote}
						WHERE id = {int:artid}',
						array('vote' => $new_voters, 'artid' => $article)
					);
				}
				// go back to the article
				redirectexit('page='.$article);
			}
		}

	}}}

    function action_show() {{{
        global $context, $scripturl, $txt;

        $db = TPDatabase::getInstance();
        // show own articles?
        // not for guests
        if($context['user']['is_guest']) {
            throw new Elk_Exception($txt['tp-noarticlesfound'], 'general');
        }

        // get all articles
        $request = $db->query('', '
            SELECT COUNT(*) FROM {db_prefix}tp_articles
            WHERE author_id = {int:author}',
            array('author' => $context['user']['id'])
        );
        $row = $db->fetch_row($request);
        $allmy = $row[0];

        $mystart = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? $_GET['p'] : 0;
        // sorting?
        $sort = $context['TPortal']['tpsort'] = (!empty($_GET['tpsort']) && in_array($_GET['tpsort'], array('date', 'id', 'subject'))) ? $_GET['tpsort'] : 'date';
        $context['TPortal']['pageindex'] = TPSubs::getInstance()->pageIndex($scripturl . '?action=tparticle;sa=myarticles;tpsort=' . $sort, $mystart, $allmy, 15);

        $context['TPortal']['subaction'] = 'myarticles';
        $context['TPortal']['myarticles'] = array();
        $request2 =  $db->query('', '
            SELECT id, subject, date, locked, approved, off FROM {db_prefix}tp_articles
            WHERE author_id = {int:author}
            ORDER BY {raw:sort} {raw:sorter} LIMIT {int:start}, 15',
            array('author' => $context['user']['id'],
            'sort' => $sort,
            'sorter' => in_array($sort, array('subject')) ? ' ASC ' : ' DESC ',
            'start' => $mystart
            )
        );

        if($db->num_rows($request2) > 0) {
            $context['TPortal']['myarticles']=array();
            while($row = $db->fetch_assoc($request2)) {
                $context['TPortal']['myarticles'][] = $row;
            }
            $db->free_result($request2);
        }

        if(loadLanguage('TPortalAdmin') == false) {
            loadLanguage('TPortalAdmin', 'english');
        }

        loadTemplate('TParticle');
        $context['sub_template'] = 'showarticle';

    }}}

}

?>
