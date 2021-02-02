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

use \TinyPortal\Model\Database as TPDatabase;
use \TinyPortal\Model\Util as TPUtil;
use \ElkArte\Errors\Errors;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Search extends \Action_Controller
{

    public function action_index() {{{

        if(\loadLanguage('TPmodules') == false) {
            \loadLanguage('TPmodules', 'english');
        }

        require_once(SUBSDIR . '/Action.class.php');
        $subActions = array (
            'searcharticle' => array($this, 'action_search', array()),
            'searchresults' => array($this, 'action_results', array()),
        );

        $sa = TPUtil::filter('sa', 'get', 'string');

        $action     = new \Action();
        $subAction  = $action->initialize($subActions, $sa);
        $action->dispatch($subAction);

    }}}

    public function action_search() {{{
        global $scripturl, $txt, $context;

        TPadd_linktree($scripturl.'?action=tportal;sa=searcharticle' , $txt['tp-searcharticles2']);
        \loadTemplate('TPSearch');
        $context['sub_template'] = 'article_search_form';

    }}}

    public function action_results() {{{
        global $scripturl, $txt, $context;

        $db = TPDatabase::getInstance();

        $start          = 0;
        $max_results    = 20;
        $usebody        = false;
        $usetitle       = false;

        if(empty($_REQUEST['start'])) {
            $start = 0;
        }
        else {
            $start = TPUtil::filter('start', 'request', 'int');
        }

        if(!empty($_REQUEST['params'])) {
            $params = TPUtil::filter('params', 'request', 'string');
            if(!empty($params)) {
                $params     = json_decode(base64_decode($params), true);
                $usebody    = $params['body'];
                $usetitle   = $params['title'];
                $what       = $params['search'];
            }
            else {
                Errors::instance()->log_error($txt['tp-nosearchentered'], false);
            }
        }
        else if(empty($_POST['tpsearch_what'])) {
            Errors::instance()->log_error($txt['tp-nosearchentered'], false);
        }
        else {
            checkSession('post');
            // clean the search
            $what = TPUtil::filter('tpsearch_what', 'post', 'string');
            if(!empty($_POST['tpsearch_title'])) {
                $usetitle = true;
            }
            if(!empty($_POST['tpsearch_body'])) {
                $usebody = true;
            }
        }

        $select     = '';
        $query      = '';
        $order_by   = '';
        if(TP_PGSQL || $context['TPortal']['fulltextsearch'] == 0) {
            if($usetitle && !$usebody) {
                $query = 'a.subject LIKE \'%' . $what . '%\'';
            }
            elseif(!$usetitle && $usebody) {
                $query = 'a.body LIKE \'%' . $what . '%\'';
            }
            elseif($usetitle && $usebody) {
                $query = 'a.subject LIKE \'%' . $what . '%\' OR a.body LIKE \'%' . $what . '%\'';
            }
            else {
                $query = 'a.subject LIKE \'%' . $what . '%\'';
            }
        }
        else {
            $splitWords = preg_split("#\s{1,}#", $what, -1);
            if(is_array($splitWords)) {
                $words  = array();
                foreach($splitWords as $word) {
                    $word       = trim($word);
                    $operator   = substr($word, 0, 1);
                    // First Character
                    switch($operator) {
                        // Allowed operators
                        case '-':
                        case '+':
                        case '>':
                        case '<':
                        case '~':
                            $word = substr($word, 1);
                            break;
                        default:
                            // Last Character of a word
                            $operator   = substr($word, -1);
                            switch($operator) {
                                // Allowed operators
                                case '-':
                                case '+':
                                case '>':
                                case '<':
                                case '~':
                                    $word = substr($word, 0, -1);
                                    break;
                                default:
                                    $operator = '';
                                    break;
                            }
                    }
                    $word       = preg_replace("#(-|\+|<|>|~|@)#s", '', $word);
                    $words[]    = $operator.$word;
                }
                $what = implode(' ',$words);
            }
            if($usetitle && !$usebody) {
                $select     = ', MATCH (subject) AGAINST (\''.$what.'\') AS score';
                $query      = 'MATCH (subject) AGAINST (\''.$what.'\' IN BOOLEAN MODE) > 0';
            }
            elseif(!$usetitle && $usebody) {
                $select     = ', MATCH (body) AGAINST (\''.$what.'\') AS score';
                $query      = 'MATCH (body) AGAINST (\''.$what.'\' IN BOOLEAN MODE) > 0';
            }
            elseif($usetitle && $usebody) {
                $select     = ', MATCH (subject, body) AGAINST (\''.$what.'\') AS score';
                $query      = 'MATCH (subject, body) AGAINST (\''.$what.'\' IN BOOLEAN MODE) > 0';
            }
            else {
                $select     = ', MATCH (subject) AGAINST (\''.$what.'\') AS score';
                $query      = 'MATCH (subject) AGAINST (\''.$what.'\' IN BOOLEAN MODE) > 0';
            }
            $order_by   = 'score DESC, ';
        }
        $num_results                            = 0;
        $context['TPortal']['searchresults']    = array();
        $context['TPortal']['searchterm']       = $what;
        $context['TPortal']['searchpage']       = $start;
        $now        = forum_time();
        $request    = $db->query('', '
            SELECT a.id, a.date, a.views, a.subject, a.body AS body, a.author_id AS author_id, a.type, m.real_name AS real_name {raw:select}
            FROM {db_prefix}tp_articles AS a
            LEFT JOIN {db_prefix}members as m ON a.author_id = m.id_member
            WHERE {raw:query}
            AND ((a.pub_start = 0 AND a.pub_end = 0)
                OR (a.pub_start != 0 AND a.pub_start < {int:now} AND a.pub_end = 0)
                OR (a.pub_start = 0 AND a.pub_end != 0 AND a.pub_end > {int:now} )
                OR (a.pub_start != 0 AND a.pub_end != 0 AND a.pub_end > {int:now} AND a.pub_start < {int:now}))
            AND a.off = 0
            ORDER BY {raw:order_by} a.date DESC LIMIT {int:limit} OFFSET {int:start}',
            array (
                'select'    => $select,
                'query'     => $query,
                'limit'     => $max_results,
                'start'     => $start,
                'now'       => $now,
                'order_by'  => $order_by,
            )
        );
        if($db->num_rows($request) > 0) {
            while($row = $db->fetch_assoc($request)) {
                TPUtil::shortenString($row['body'], 300);
                if($row['type'] == 'bbc') {
                    $row['body'] = parse_bbc($row['body']);
                }
                elseif($row['type'] == 'php') {
                    $row['body'] = '[PHP]';
                }
                else {
                    $row['body'] = strip_tags($row['body']);
                }

                $row['subject'] = preg_replace('/'.preg_quote($what, '/').'/', '<span class="highlight">'.$what.'</span>', $row['subject']);
                $row['body']    = preg_replace('/'.preg_quote($what, '/').'/', '<span class="highlight">'.$what.'</span>', $row['body']);
                $context['TPortal']['searchresults'][]=array(
                    'id' 		=> $row['id'],
                    'date' 		=> $row['date'],
                    'views' 	=> $row['views'],
                    'subject' 	=> $row['subject'],
                    'body' 		=> $row['body'],
                    'author' 	=> '<a href="'.$scripturl.'?action=profile;u='.$row['author_id'].'">'.$row['real_name'].'</a>',
                );
            }
            $db->free_result($request);
        }

        $request    = $db->query('', '
            SELECT COUNT(id) AS num_results
            FROM {db_prefix}tp_articles AS a
            LEFT JOIN {db_prefix}members as m ON a.author_id = m.id_member
            WHERE {raw:query}
            AND ((a.pub_start = 0 AND a.pub_end = 0)
                OR (a.pub_start != 0 AND a.pub_start < {int:now} AND a.pub_end = 0)
                OR (a.pub_start = 0 AND a.pub_end != 0 AND a.pub_end > {int:now} )
                OR (a.pub_start != 0 AND a.pub_end != 0 AND a.pub_end > {int:now} AND a.pub_start < {int:now}))
            AND a.off = 0',
            array (
                'query'     => $query,
                'now'       => $now,
            )
        );

        $num_results = $db->fetch_assoc($request)['num_results'];
        $db->free_result($request);

        $params = base64_encode(json_encode(array( 'search' => $what, 'title' => $usetitle, 'body' => $usebody)));

        // Now that we know how many results to expect we can start calculating the page numbers.
        $context['page_index']  = constructPageIndex($scripturl . '?action=tpsearch;sa=searchresults;params=' . $params, $start, $num_results, $max_results, false);


        $context['sub_template'] = 'article_search_results';
        \loadTemplate('TPSearch');

    }}}

}

?>
