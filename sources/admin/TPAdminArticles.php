<?php

/**
 * @package "TPArticleAdmin" Addon for Elkarte
 * @author tinoest
 * @license BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * @version 1.0.0
 *
 */

if (!defined('ELK'))
{
	die('No access...');
}

class TPAdminArticles_Controller extends Action_Controller
{
	public function action_index() {{{

        if (!allowedTo('admin_forum')) {
            isAllowedTo('tp_articles');
        }

        require_once(SUBSDIR . '/Action.class.php');

		$subActions = array(
			'index' 		    => array($this, 'action_default'),
			'listarticle' 		=> array($this, 'action_list_article'),
			'editarticle' 		=> array($this, 'action_edit_article'),
			'deletearticle'		=> array($this, 'action_delete_article'),
			'listcategory' 		=> array($this, 'action_list_category'),
			'addcategory' 		=> array($this, 'action_add_category'),
			'editcategory' 		=> array($this, 'action_edit_category'),
			'deletecategory' 	=> array($this, 'action_delete_category'),
		);

		$action     = new Action('');
		$subAction  = $action->initialize($subActions, 'index');

		$action->dispatch($subAction);
	}}}

	public function action_default() {{{

		$this->action_list_article();
	}}}

	public function action_admin_menu() {{{
	
    }}}

	public function action_list_article() {{{
	
    }}}

	public function action_edit_article() {{{
	
    }}}

	public function action_delete_article() {{{

		// Just Load the list again
		$this->action_list_article();
	}}}

	public function action_list_category() {{{
	
    }}}

	public function action_add_category() {{{

	}}}

	public function action_edit_category() {{{
		
		// Just Load the list again
		$this->action_list_category();
	}}}

	public function action_delete_category() {{{

		// Just Load the list again
		$this->action_list_category();
	}}}

	public function list_articles($start, $items_per_page, $sort) {{{

	}}}

	public function list_total_articles() {{{

	}}}

	public function list_categories($start, $items_per_page, $sort) {{{

	}}}

	public function list_total_categories() {{{

	}}}
}
