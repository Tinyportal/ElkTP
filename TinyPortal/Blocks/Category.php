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

    public function setup( &$block ) {{{

        $block['title'] = '<span class="header">' . $block['title'] . '</span>';
        $this->context['TPortal']['blocklisting'] = $block['body'];
        $this->context['TPortal']['blocklisting_height'] = $block['var1'];
        $this->context['TPortal']['blocklisting_author'] = $block['var2'];

    }}}

    function display( $block ) {{{

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
