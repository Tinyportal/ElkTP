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
namespace TinyPortal\Model;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class chain
{
   public $table;
   public $rows;
   public $chain_table;
   public $primary_field;
   public $parent_field;
   public $sort_field;

   public function __construct($primary_field, $parent_field, $sort_field, $rows, $root_id, $maxlevel) {{{
       $this->rows = $rows;
       $this->primary_field = $primary_field;
       $this->parent_field = $parent_field;
       $this->sort_field = $sort_field;
       $this->buildChain($root_id,$maxlevel);
   }}}

   public function buildChain($rootcatid,$maxlevel) {{{
       foreach($this->rows as $row) {
           $this->table[$row[$this->parent_field]][ $row[$this->primary_field]] = $row;
       }
       $this->makeBranch($rootcatid, 0, $maxlevel);
   }}}

   public function makeBranch($parent_id, $level, $maxlevel) {{{
       if(!is_array($this->table)) {
              $this->table = array();
        }

       if(!array_key_exists($parent_id, $this->table)) {
              return;
        }

       $rows = $this->table[$parent_id];
       foreach($rows as $key=>$value) {
           $rows[$key]['key'] = $this->sort_field;
       }

       usort($rows, 'self::chainCMP');
       foreach($rows as $item) {
           $item['indent'] = $level;
           $this->chain_table[] = $item;
           if((isset($this->table[$item[$this->primary_field]])) && (($maxlevel > $level + 1) || ($maxlevel == 0))) {
               $this->makeBranch($item[$this->primary_field], $level + 1, $maxlevel);
           }
       }

   }}}

    public function chainCMP($a, $b) {{{
       if($a[$a['key']] == $b[$b['key']]) {
           return 0;
       }
       return($a[$a['key']] < $b[$b['key']]) ? -1 : 1;
    }}}
}

?>
