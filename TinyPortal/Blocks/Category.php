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
namespace TinyPortal\Blocks;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Category extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function prepare( &$block ) {{{

	}}}
	
    public function setup( &$block ) {{{

        $categories = \TinyPortal\Model\Article::getInstance()->getArticlesInCategory($block['category'], false, true);
        if (!isset($block['data'])) {
            $block['data'] = array();
        }

        if(is_array($categories)) {
            foreach($categories as $row) {
                if(empty($row['author'])) {
                    global $memberContext;
                    // Load their context data.
                    if(!array_key_exists('admin_features', $this->context)) {
                        $this->context['admin_features']    = array();
                        $adminFeatures                      = true;
                    }
                    else {
                        $adminFeatures                      = false;
                    }

					\ElkArte\MembersList::load($row['author_id'], false, 'normal');
					(\ElkArte\MembersList::get($row['author_id']))->loadContext(true);

                    if($adminFeatures == true) {
                        unset($this->context['admin_features']);
                    }
                    $row['real_name'] = (!is_null($memberContext)) ? $memberContext[$row['author_id']]['username'] : '';
                }
                else {
                    $row['real_name'] = $row['author'];
                }
                $block['data'][$row['category']][$row['date'].'_'.$row['id']] = array(
                    'id'        => $row['id'],
                    'subject'   => $row['subject'],
                    'shortname' => $row['shortname']!='' ?$row['shortname'] : $row['id'] ,
                    'category'  => $row['category'],
                    'poster'    => '<a href="'.$this->scripturl.'?action=profile;u='.$row['author_id'].'">'.$row['real_name'].'</a>',
                );
            }
        }

        $block['title'] = '<span class="header">' . $block['title'] . '</span>';

    }}}

    public function display( $block ) {{{

        if(isset($block['data'][$block['category']])){
            echo '<div class="middletext" ', (count($block['data'][$block['category']]) > $block['height'] && $block['height'] != '0' ) ? ' style="overflow: auto; width: 100%; height: '.$block['height'].'em;"' : '' ,'>';
            foreach($block['data'][$block['category']] as $listing){
                if($listing['category'] == $block['category']) {
                    echo '<b><a href="'.$this->scripturl.'?page='.$listing['shortname'].'">'.$listing['subject'].'</a></b> ' , $block['author'] == '1' ? $this->txt['by'].' '.$listing['poster'] : '' , '<br>';
                }
            }
            echo '</div>';
        }

    }}}

    public function admin_setup( &$block ) {{{

		parent::admin_setup($block);

		$default = array(
			'category'	=> 0,
			'height'	=> 0,
			'author'	=> 1,
		);

		if(empty($block['settings'])) {
			$block += $default;
		}

    }}}

    public function admin_display( $block ) {{{

		echo '
		<hr>
		<dl class="tptitle settings">
			<dt>
				<label for="tp_block_set_category">'.$this->txt['tp-showcategory'].'</label>
			</dt>
			<dd>
				<select name="tp_block_set_category" id="tp_block_set_category">
					<option value="0">'.$this->txt['tp-none2'].'</option>';
					foreach($this->context['TPortal']['catnames'] as $cat => $catname) {
						echo '<option value="'.$cat.'" ' , ($block['category'] == $cat) ? ' selected' : '' ,' >'.html_entity_decode($catname).'</option>';
					}
		echo '
				</select>
			</dd>
			<dt>
				<label for="tp_block_set_height">'.$this->txt['tp-catboxheight'].'</label>
			</dt>
			<dd>
				<input type="number" id="tp_block_set_height" name="tp_block_set_height" value="' , ((!is_numeric($block['height'])) || (($block['height']) == 0) ? '15' : $block['height']) ,'" style="width: 6em" min="1" required> em
			</dd>
			<dt>
				<label for="field_name">'.$this->txt['tp-catboxauthor'].'</label>
			</dt>
			<dd>
				<input type="radio" name="tp_block_set_author" value="1" ' , $block['author']=='1' ? 'checked' : '' ,'> ', $this->txt['tp-yes'], '<br>
				<input type="radio" name="tp_block_set_author" value="0" ' , $block['author']=='0' ? 'checked' : '' ,'> ', $this->txt['tp-no'], '
			</dd>
		</dl>';

    }}}

}

?>
