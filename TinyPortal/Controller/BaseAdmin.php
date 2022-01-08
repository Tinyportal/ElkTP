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

class BaseAdmin extends \ElkArte\AbstractController
{
    protected $context;
    protected $scripturl;
    protected $txt;

    public function __construct() {{{
        global $context, $scripturl, $txt;

        $this->context      = &$context;
        $this->scripturl    = &$scripturl;
        $this->txt          = &$txt;

    }}}

    public function action_index() {{{

    }}}

    public function make_list( $name = 'default' ) {{{

		$list = array (
			'id' => $name.'_list',
			'title' => $this->txt['tp-'.$name],
			'items_per_page' => 25,
			'no_items_label' => $this->txt['tp-notfound'],
			'base_href' => $this->scripturl . '?action=admin;area=tp'.$name.';sa=list;',
			'default_sort_col' => 'title',
			'get_items' => array(
				'function' => array($this, 'list_'.$name),
			),
			'get_count' => array(
				'function' => array($this, 'list_total_'.$name),
			),
			'columns' => array(
				'title' => array(
					'header' => array(
						'value' => $this->txt['tp-'.$name.'title'],
					),
					'data' => array(
						'db' => 'title',
					),
					'sort' => array(
						'default' => 'title ASC',
						'reverse' => 'title DESC',
					),
				),

				'category' => array(
					'header' => array(
						'value' => $this->txt['tp-'.$name.'category'],
					),
					'data' => array(
						'db' => 'category',
					),
					'sort' => array(
						'default' => 'category_id ASC',
						'reverse' => 'category_id DESC',
					),
				),
				'author' => array(
					'header' => array(
						'value' => $this->txt['tp-'.$name.'author'],
					),
					'data' => array(
						'db' => 'member',
					),
					'sort' => array(
						'default' => 'member_id ASC',
						'reverse' => 'member_id DESC',
					),
				),
				'date' => array(
					'header' => array(
						'value' => $this->txt['tp-'.$name.'date'],
					),
					'data' => array(
						'db' => 'dt_published',
					),
					'sort' => array(
						'default' => 'dt_published ASC',
						'reverse' => 'dt_published DESC',
					),
				),
				'status' => array(
					'header' => array(
						'value' => $this->txt['tp-'.$name.'status'],
						'class' => 'centertext',
					),
					'data' => array(
						'db' => 'status',
						'class' => 'centertext',
					),
					'sort' => array(
						'default' => 'status',
						'reverse' => 'status DESC',
					),
				),
				'action' => array(
					'header' => array(
						'value' => $this->txt['tp-'.$name.'actions'],
						'class' => 'centertext',
					),
					'data' => array(
						'sprintf' => array (
							'format' => '
								<a href="?action=admin;area=tp'.$name.';sa=edit;id=%1$d;' . $this->context['session_var'] . '=' . $this->context['session_id'] . '" accesskey="p">Modify</a>&nbsp;
								<a href="?action=admin;area=tp'.$name.';sa=delete;id=%1$d;' . $this->context['session_var'] . '=' . $this->context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape('Are you sure you want to delete?') . ') && submitThisOnce(this);" accesskey="d">Delete</a>',
							'params' => array(
								'id' => true,
							),
						),
						'class' => 'centertext nowrap',
					),
				),
			),
			'form' => array(
				'href' => $this->scripturl . '?action=admin;area=tp'.$name.';sa=add;',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$this->context['session_var'] => $this->context['session_id'],
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="action_edit" value="' . $this->txt['tp-add'.$name] . '" class="right_submit" />',
				),
			),
		);

		$this->context['page_title']	= $this->txt['tp-'.$name.'list'];
		$this->context['default_list'] 	= $name.'_list';

		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($list);

        $this->context['sub_template'] = 'list_'.$name;
    }}}
}

?>
