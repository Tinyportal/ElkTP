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

class Catmenu extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        $block['title'] = '<span class="header">' . $block['title'] . '</span>';
        $block['title'] = '<span class="header">' . $block['title'] . '</span>';
        $this->context['TPortal']['menuid']     = is_numeric($block['body']) ? $block['body'] : 0;
        $this->context['TPortal']['menuvar1']   = $block['var1'];
        $this->context['TPortal']['menuvar2']   = $block['var2'];
        $this->context['TPortal']['blockid']    = $block['id'];

    }}}

    public function display( $block ) {{{

        if(isset($this->context['TPortal']['menu'][$this->context['TPortal']['menuid']]) && !empty($this->context['TPortal']['menu'][$this->context['TPortal']['menuid']])){
            echo '
                <ul class="tp_catmenu">';

            foreach($this->context['TPortal']['menu'][$this->context['TPortal']['menuid']] as $cn) {
                echo '
                    <li', $cn['type']=='head' ? ' class="tp_catmenu_header"' : '' ,'>';
                if($this->context['TPortal']['menuvar1'] == '' || $this->context['TPortal']['menuvar1'] == '0') {
                    echo str_repeat("&nbsp;&nbsp;", ($cn['sub'] + 1));
                }
                elseif($this->context['TPortal']['menuvar1'] == '1') {
                    echo str_repeat("&nbsp;&nbsp;", ($cn['sub'] + 1));
                }
                elseif($this->context['TPortal']['menuvar1'] == '2') {
                    echo str_repeat("&nbsp;&nbsp;", ($cn['sub'] + 1));
                }

                if((!isset($cn['icon']) || (isset($cn['icon']) && $cn['icon'] == '')) && $cn['type'] != 'head' && $cn['type'] != 'spac') {
                    if($this->context['TPortal']['menuvar1'] == '' || $this->context['TPortal']['menuvar1'] == '0') {
                        echo '
                            <img src="'.$settings['tp_images_url'].'/TPdivider2.png" alt="" />&nbsp;';
                    }
                    elseif($this->context['TPortal']['menuvar1'] == '1') {
                        echo '
                        <img src="'.$settings['tp_images_url'].'/bullet3.png" alt="" />';
                    }
                }
                elseif(isset($cn['icon']) && $cn['icon'] != '' && $cn['type'] != 'head' && $cn['type'] != 'spac') {
                    echo '
                        <img alt="*" src="'.$cn['icon'].'" />&nbsp;';
                }
                switch($cn['type']) {
                    case 'cats' :
                        echo '
                            <a href="'. $this->scripturl. '?cat='.$cn['IDtype'].'"' .( $cn['newlink']=='1' ? ' target="_blank"' : ''). '>'.$cn['name'].'</a>';
                        break;
                    case 'arti' :
                        echo '
                            <a href="'. $this->scripturl. '?page='.$cn['IDtype'].'"' .($cn['newlink']=='1' ? ' target="_blank"' : '') . '>'.$cn['name'].'</a>';
                        break;
                    case 'link' :
                        echo '
                            <a href="'.$cn['IDtype'].'"' . ($cn['newlink']=='1' ? ' target="_blank"' : '') . '>'.$cn['name'].'</a>';
                        break;
                    case 'head' :
                        echo '
                            <a class="tp_catmenu_header" name="header'.$cn['id'].'"><b>'.$cn['IDtype'].'</b></a>';
                        break;
                    case 'spac' :
                        echo '
                            <a name="spacer'.$cn['id'].'">&nbsp;</a>';
                        break;
                    default :
                        echo '
                            <a href="'.$cn['IDtype'].'"' . ($cn['newlink']=='1' ? ' target="_blank"' : '') . '>'.$cn['name'].'</a>';
                        break;
                }
                echo '</li>';
            }
            echo '
                </ul>';
        }

    }}}

    public function admin_setup( &$block ) {{{

		parent::admin_setup($block);

    }}}

    public function admin_display( $block ) {{{

		return false;

    }}}

}

?>
