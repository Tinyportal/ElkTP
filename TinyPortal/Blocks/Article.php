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

class Article extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function prepare( &$block ) {{{

        if(!is_numeric($block['body'])) {
            return;
        }

        $this->context['TPortal']['blockarticles'] = array();
        $articles   = \TinyPortal\Model\Article::getInstance()->getArticle($block['body']);
        if(is_array($articles)) {
            foreach($articles as $article) {
                // allowed and all is well, go on with it.
                $this->context['TPortal']['blockarticles'][$article['id']] = $article;
                // setup the avatar code
                if ($this->modSettings['avatar_action_too_large'] == 'option_html_resize' || $this->modSettings['avatar_action_too_large'] == 'option_js_resize') {
                    $avatar_width   = !empty($this->modSettings['avatar_max_width_external']) ? ' width="' . $this->modSettings['avatar_max_width_external'] . '"' : '';
                    $avatar_height  = !empty($this->modSettings['avatar_max_height_external']) ? ' height="' . $this->modSettings['avatar_max_height_external'] . '"' : '';
                }
                else {
                    $avatar_width   = '';
                    $avatar_height  = '';
                }

                $this->context['TPortal']['blockarticles'][$article['id']]['avatar'] = determineAvatar( array(
                            'avatar'            => $article['avatar'],
                            'email_address'     => $article['email_address'],
                            'filename'          => !empty($article['filename']) ? $article['filename'] : '',
                            'id_attach'         => $article['id_attach'],
                            'attachment_type'   => $article['attachment_type'],
                        )
                )['image'];
                // sort out the options
                $this->context['TPortal']['blockarticles'][$article['id']]['visual_options'] = array();
                // since these are inside blocks, some stuff has to be left out
                $this->context['TPortal']['blockarticles'][$article['id']]['frame'] = 'none';
            }
        }

    }}}

    public function setup( &$block ) {{{

        $block['title'] = '<span class="header">' . $block['title'] . '</span>';
        $this->context['TPortal']['blockarticle'] = $block['body'];

    }}}

    public function display( $block ) {{{

        if(isset($this->context['TPortal']['blockarticles'][$this->context['TPortal']['blockarticle']])) {
		    echo '<div class="block_article">';
            if(!empty($this->context['TPortal']['blockarticles'][$this->context['TPortal']['blockarticle']]['template'])) {
                \TinyPortal\Model\Subs::getInstance()->render_template($this->context['TPortal']['blockarticles'][$this->context['TPortal']['blockarticle']]['template']);
            }
            else {
                \TinyPortal\Model\Subs::getInstance()->render_template(blockarticle_renders());
            }
            echo '</div>';
        }

    }}}

    public function admin_setup( &$block ) {{{

		parent::admin_setup($block);

    }}}

    public function admin_display( $block ) {{{

			echo '</div><div>
					<hr>
					<dl class="tptitle settings">
						<dt>
							<label for="field_name">',$this->txt['tp-showarticle'],'</label>
						</dt>
						<dd>
							<select name="tp_block_body">
								<option value="0">'.$this->txt['tp-none2'].'</option>';
							foreach($this->context['TPortal']['edit_articles'] as $art => $article ){
								echo '<option value="'.$article['id'].'" ' , $block['body']==$article['id'] ? ' selected="selected"' : '' ,' >'.html_entity_decode($article['subject']).'</option>';
							}
						echo '</select>
						</dd>
					</dl>';

    }}}

}

?>
