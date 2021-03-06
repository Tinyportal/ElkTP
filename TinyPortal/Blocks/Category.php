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

        $categories = \TinyPortal\Model\Articles::getInstance()->getArticlesInCategory($block['body'], false, true);
        if (!isset($context['TPortal']['blockarticle_titles'])) {
            $context['TPortal']['blockarticle_titles'] = array();
        }

        if(is_array($categories)) {
            foreach($categories as $row) {
                if(empty($row['author'])) {
                    global $memberContext;
                    // Load their context data.
                    if(!array_key_exists('admin_features', $context)) {
                        $context['admin_features']  = array();
                        $adminFeatures              = true;
                    }
                    else {
                        $adminFeatures              = false;
                    }

                    \loadMemberData($row['author_id'], false, 'normal');
                    \loadMemberContext($row['author_id']);

                    if($adminFeatures == true) {
                        unset($context['admin_features']);
                    }
                    $row['real_name'] = $memberContext[$row['author_id']]['username'];
                }
                else {
                    $row['real_name'] = $row['author'];
                }
                $context['TPortal']['blockarticle_titles'][$row['category']][$row['date'].'_'.$row['id']] = array(
                    'id'        => $row['id'],
                    'subject'   => $row['subject'],
                    'shortname' => $row['shortname']!='' ?$row['shortname'] : $row['id'] ,
                    'category'  => $row['category'],
                    'poster'    => '<a href="'.$scripturl.'?action=profile;u='.$row['author_id'].'">'.$row['real_name'].'</a>',
                );
            }
        }

    }}}

    public function setup( &$block ) {{{

        $block['title'] = '<span class="header">' . $block['title'] . '</span>';
        $this->context['TPortal']['blocklisting'] = $block['body'];
        $this->context['TPortal']['blocklisting_height'] = $block['var1'];
        $this->context['TPortal']['blocklisting_author'] = $block['var2'];

    }}}

    public function display( $block ) {{{

        if(isset($this->context['TPortal']['blockarticle_titles'][$this->context['TPortal']['blocklisting']])){
            echo '<div class="middletext" ', (count($this->context['TPortal']['blockarticle_titles'][$this->context['TPortal']['blocklisting']])>$this->context['TPortal']['blocklisting_height'] && $this->context['TPortal']['blocklisting_height']!='0') ? ' style="overflow: auto; width: 100%; height: '.$this->context['TPortal']['blocklisting_height'].'em;"' : '' ,'>';
            foreach($this->context['TPortal']['blockarticle_titles'][$this->context['TPortal']['blocklisting']] as $listing){
                if($listing['category'] == $this->context['TPortal']['blocklisting'])
                    echo '<b><a href="'.$this->scripturl.'?page='.$listing['shortname'].'">'.$listing['subject'].'</a></b> ' , $this->context['TPortal']['blocklisting_author']=='1' ? $this->txt['by'].' '.$listing['poster'] : '' , '<br>';
            }
            echo '</div>';
        }

    }}}

}

?>
