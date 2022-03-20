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
namespace TinyPortal\Model;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Subs
{
    private static $_instance   = null;

    public static function getInstance() {{{

    	if(self::$_instance == null) {
			self::$_instance = new self();
		}

    	return self::$_instance;

    }}}

    // Empty Clone method
    private function __clone() { }

    public function loadCSS() {{{
        global $context, $settings, $boardurl;

        $context['html_headers'] .=  "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/>";

        // load both stylesheets to be sure all is in, but not if things aren't setup!
        if(!empty($settings['default_theme_url']) && !empty($settings['theme_url']) && file_exists($settings['theme_dir'].'/css/tp-style.css')) {
            $context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/tp-style.css?'.TP_SHORT_VERSION.'" />';
        }
        else {
            $context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="'.$boardurl.'/TinyPortal/Views/css/tp-style.css?'.TP_SHORT_VERSION.'" />';
        }

        if(!empty($settings['default_theme_url']) && !empty($settings['theme_url']) && file_exists($settings['theme_dir'].'/css/tp-responsive.css')) {
            $context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/tp-responsive.css?'.TP_SHORT_VERSION.'" />';
        }
        else {
            $context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="'.$boardurl.'/TinyPortal/Views/css/tp-responsive.css?'.TP_SHORT_VERSION.'" />';
        }

        if(!empty($settings['default_theme_url']) && !empty($settings['theme_url']) && file_exists($settings['theme_dir'].'/css/tp-custom.css')) {
            $context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/tp-custom.css?'.TP_SHORT_VERSION.'" />';
        }
        else {
            $context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="'.$boardurl.'/TinyPortal/Views/css/tp-custom.css?'.TP_SHORT_VERSION.'" />';
        }

        if(!empty($context['TPortal']['padding'])) {
            $context['html_headers'] .= '
                <style type="text/css">
                    .block_leftcontainer,
                    .block_rightcontainer,
                    .block_topcontainer,
                    .block_uppercontainer,
                    .block_centercontainer,
                    .block_frontcontainer,
                    .block_lowercontainer,
                    .block_bottomcontainer {
                        padding-bottom: ' . $context['TPortal']['padding'] . 'px;
                    }

                    #tpleftbarHeader {
                        margin-right: ' . $context['TPortal']['padding'] . 'px;
                    }

                    #tprightbarHeader {
                        margin-left: ' . $context['TPortal']['padding'] . 'px;
                    }

                </style>';
        }

    }}}

    public function setupSettings() {{{
        global $maintenance, $context, $txt, $settings, $modSettings, $boardurl;

        $db = Database::getInstance();

        $context['TPortal']['always_loaded'] = array();

        // Try to load it from the cache
        if (($context['TPortal'] = \ElkArte\Cache\Cache::instance()->get('tpSettings', 90)) == null) {
            $context['TPortal']  = Admin::getInstance()->getSetting();
            if (!empty($modSettings['cache_enable'])) {
                \ElkArte\Cache\Cache::instance()->put('tpSettings', $context['TPortal'], 90);
            }
        }

        // setup the userbox settings
        $userbox = explode(',', $context['TPortal']['userbox_options']);
        foreach($userbox as $u => $val) {
            $context['TPortal']['userbox'][$val] = 1;
        }

        // setup sizes for DL and articles
        $context['TPortal']['art_imagesize'] = explode(',', $context['TPortal']['art_imagesizes']);

        // another special case: sitemap items
        $context['TPortal']['sitemap'] = array();
        foreach($context['TPortal'] as $what => $value) {
            if(substr($what, 0, 14) == 'sitemap_items_' && !empty($value)) {
                $context['TPortal']['sitemap_items'] .= ','. $value;
            }
        }

        if(isset($context['TPortal']['sitemap_items'])) {
            $context['TPortal']['sitemap'] = explode(',', $context['TPortal']['sitemap_items']);
        }

        // yet another special case: category list
        $context['TPortal']['category_list'] = array();
        if(isset($context['TPortal']['cat_list'])) {
            $context['TPortal']['category_list'] = explode(',', $context['TPortal']['cat_list']);
        }

        // setup path for TP images, fallback on default theme - but not if its set already!
        if(!isset($settings['tp_images_url'])) {
            // check if the them has a folder
            if(file_exists($settings['theme_dir'].'/images/tinyportal/TParticle.png')) {
                $settings['tp_images_url'] = $settings['images_url'] . '/tinyportal';
            }
            else {
                $settings['tp_images_url'] = $boardurl . '/TinyPortal/Views/images';
            }
        }

        // hooks setting up
        $context['TPortal']['hooks'] = array(
            'topic_check' => array(),
            'board_check' => array(),
            'tp_layer' => 'tp',
            'tp_block' => 'TPblock',
        );


        // start of things
        $context['TPortal']['mystart'] = 0;
        if(isset($_GET['p']) && $_GET['p'] != '' && is_numeric($_GET['p'])) {
            $context['TPortal']['mystart'] = Util::filter('p', 'get', 'int');
        }

        $context['tp_html_headers'] = '';

        // any sorting taking place?
        if(isset($_GET['tpsort'])) {
            $context['TPortal']['tpsort'] = $_GET['tpsort'];
        }
        else {
            $context['TPortal']['tpsort'] = '';
        }

        // if not in forum start off empty
        $context['TPortal']['is_front'] = false;
        $context['TPortal']['is_frontpage'] = false;
        if(!isset($_GET['action']) && !isset($_GET['board']) && !isset($_GET['topic'])) {
            $this->strip_linktree();
            // a switch to make it clear what is "forum" and not
            $context['TPortal']['not_forum'] = true;
        }
        // are we actually on frontpage then?
        if(!isset($_GET['cat']) && !isset($_GET['page']) && !isset($_GET['action'])) {
            $context['TPortal']['is_front'] = true;
            $context['TPortal']['is_frontpage'] = true;
        }

        // Set the page title.
        if($context['TPortal']['is_front'] && !empty($context['TPortal']['frontpage_title'])) {
            $context['page_title'] = $context['TPortal']['frontpage_title'];
        }

        if(isset($_GET['action']) && $_GET['action'] == 'tpadmin') {
            $context['page_title'] = $context['forum_name'] . ' - ' . $txt['tp-admin'];
        }

        // if we are in maintance mode, just hide panels
        if (!empty($maintenance) && !allowedTo('admin_forum')) {
            $this->hidebars('all');
        }

        // save the action value
        $context['TPortal']['action'] = !empty($_GET['action']) ? Util::filter('action', 'get', 'string') : '';

        // save the frontapge setting for ELK
        $settings['TPortal_front_type'] = $context['TPortal']['front_type'];
        if(empty($context['page_title'])) {
            $context['page_title'] = $context['forum_name'];
        }

    }}}

    public function permaTheme($theme) {{{
        global $context;

        $db = Database::getInstance();

        $me = $context['user']['id'];
        $db->query('', '
            UPDATE {db_prefix}members
            SET id_theme = {int:theme}
            WHERE id_member = {int:mem_id}',
            array(
                'theme' => $theme, 'mem_id' => $me,
            )
        );

        if(isset($context['TPortal']['querystring'])) {
            $tp_where = str_replace(array(';permanent'), array(''), $context['TPortal']['querystring']);
        }
        else {
            $tp_where = 'action=forum;';
        }

        redirectexit($tp_where);
    }}}

    // TPortal side bar, left or right.
    public function panel($side) {{{
        global $context, $scripturl, $settings;

        // decide for $flow
        $flow = $context['TPortal']['block_layout_' . $side];

        $panelside = $paneltype = ($side == 'front' ? 'frontblocks' : 'blocks');

        // set the grid type
        if($flow == 'grid') {
            $grid_selected = $context['TPortal']['blockgrid_' . $side];
            if($grid_selected == 'colspan3') {
                $grid_recycle = 4;
            }
            elseif($grid_selected == 'rowspan1') {
                $grid_recycle = 5;
            }

            $grid_entry = 0;
            // fetch the grids..
            self::blockGrids();
        }

        // check if we left out the px!!
        if(is_numeric($context['TPortal']['blockwidth_'.$side])) {
            $context['TPortal']['blockwidth_'.$side] .= 'px';
        }

        // for the cols, calculate numbers
        if($flow == 'horiz2') {
            $flowgrid = array(
                '1' => array(1, 0),
                '2' => array(1, 1),
                '3' => array(2, 1),
                '4' => array(2, 2),
                '5' => array(3, 2),
                '6' => array(3, 3),
                '7' => array(4, 3),
                '8' => array(4, 4),
                '9' => array(5, 4),
                '10' => array(5, 5),
                '11' => array(6, 5),
                '12' => array(6, 6),
                '13' => array(7, 6),
                '14' => array(7, 7),
                '15' => array(8, 7),
                '16' => array(8, 8),
            );
        }
        elseif($flow == 'horiz3') {
            $flowgrid = array(
                '1' => array(1, 0, 0),
                '2' => array(1, 1, 0),
                '3' => array(1, 1, 1),
                '4' => array(2, 1, 1),
                '5' => array(2, 2, 1),
                '6' => array(2, 2, 2),
                '7' => array(3, 2, 2),
                '8' => array(3, 3, 2),
                '9' => array(3, 3, 3),
                '10' => array(4, 3, 3),
                '11' => array(4, 4, 3),
                '12' => array(4, 4, 4),
                '13' => array(5, 4, 4),
                '14' => array(5, 5, 4),
                '15' => array(5, 5, 5),
                '16' => array(6, 5, 5),
            );
        }
        elseif($flow == 'horiz4') {
            $flowgrid = array(
                '1' => array(1, 0, 0, 0),
                '2' => array(1, 1, 0, 0),
                '3' => array(1, 1, 1, 0),
                '4' => array(1, 1, 1, 1),
                '5' => array(2, 1, 1, 1),
                '6' => array(2, 2, 1, 1),
                '7' => array(2, 2, 2, 1),
                '8' => array(2, 2, 2, 2),
                '9' => array(3, 2, 2, 2),
                '10' => array(3, 3, 2, 2),
                '11' => array(3, 3, 3, 2),
                '12' => array(3, 3, 3, 3),
                '13' => array(4, 3, 3, 3),
                '14' => array(4, 4, 3, 3),
                '15' => array(4, 4, 4, 3),
                '16' => array(4, 4, 4, 4),
            );
        }

        if(in_array($flow, array('horiz2', 'horiz3', 'horiz4'))) {
            $pad = $context['TPortal']['padding'];
            switch($flow) {
                case 'horiz2':
                    $wh = 50;
                    break;
                case 'horiz3':
                    $wh = 33;
                    break;
                case 'horiz4':
                    $wh = 25;
                    break;
            }
            echo '<div style="width:100%;"><div class="panelsColumns" style="' . (isset($wh) ? 'width: '.$wh.'%;' : '' ) . 'padding-right: '.$pad.'px;float:left;">';
        }
        $flowmain = 0;
        $flowsub = 0;
        $bcount = 0;
        $flowcount = isset($context['TPortal'][$panelside][$side]) ? count($context['TPortal'][$panelside][$side]) : 0;
        if(!isset($context['TPortal'][$panelside][$side])) {
            $context['TPortal'][$panelside][$side] = array();
        }

        $n = count($context['TPortal'][$paneltype][$side]);
        $context['TPortal'][$panelside][$side] = (array) $context['TPortal'][$panelside][$side];
        foreach ($context['TPortal'][$panelside][$side] as $i => &$block) {
            if(!isset($block['frame'])) {
                continue;
            }

            $theme = $block['frame'] == 'theme';

            // check if a language title string exists
            $newtitle = $this->getlangOption($block['lang'], $context['user']['language']);
            if(!empty($newtitle)) {
                $block['title'] = $newtitle;
            }

            $blockClass = '\TinyPortal\Blocks\\'.ucfirst($block['type']);
            if(class_exists($blockClass)) {
                (new $blockClass)->setup($block);
            }

            // render them horisontally
            if($flow == 'horiz') {
                $pad = $context['TPortal']['padding'];
                if($i == ($flowcount-1)) {
                    $pad=0;
                }
                echo '<div class="panelsColumnsHorizontally" style="float: left; width: ' . $context['TPortal']['blockwidth_'.$side].';"><div style="padding-right: ' . $pad . 'px;">';
                call_user_func($context['TPortal']['hooks']['tp_block'], $block, $theme, $side);
                echo '</div></div>';
            }
            // render them horisontally
            elseif(in_array($flow, array('horiz2', 'horiz3', 'horiz4'))) {
                $pad = $context['TPortal']['padding'];
                if($flow == 'horiz2') {
                    $wh = 50;
                }
                elseif($flow == 'horiz3') {
                        $wh = 33;
                }
                elseif($flow == 'horiz4') {
                    $wh = 25;
                }

                if(isset($flowgrid) && $flowsub == $flowgrid[$flowcount][$flowmain]) {
                    $flowsub = 0;
                    $flowmain++;
                    if($flow == 'horiz2' && $flowmain == 1) {
                        $pad = 0;
                    }
                    elseif($flow == 'horiz3' && $flowmain == 2) {
                        $pad = 0;
                        $wh = 34;
                    }
                    elseif($flow == 'horiz4' && $flowmain == 3) {
                        $pad = 0;
                    }
                    echo '</div><div class="panelsColumns" style="' . (isset($wh) ? 'width: '. $wh.'%;' : '') .  'padding-right: '.$pad.'px;float:left;">';
                }
                call_user_func($context['TPortal']['hooks']['tp_block'], $block, $theme, $side);
            }
            // according to a grid
            elseif($flow == 'grid') {
                echo self::blockGrid($block, $theme, $grid_entry, $side, $grid_entry == ($grid_recycle - 1) ? true : false, $grid_selected);
                $grid_entry++;
                if($grid_recycle == $grid_entry) {
                    $grid_entry = 0;
                }
                // what if its the last block, but in the middle of the recycle?
                if($i == $n - 1) {
                    if($grid_entry > 0) {
                        for($a = $grid_entry; $a < $grid_recycle; $a++) {
                            echo self::blockGrid(0, 0, $a, $side, $a == ($grid_recycle-1) ? true : false, $grid_selected,true);
                        }
                    }
                }
            }
            // or just plain vertically
            else {
                call_user_func($context['TPortal']['hooks']['tp_block'], $block, $theme, $side);
            }

            $bcount++;
            $flowsub++;
        }
        if(in_array($flow, array('horiz2', 'horiz3', 'horiz4'))) {
            echo '</div><p class="clearthefloat"></p></div>';
        }

    }}}

    public function setupUpshrinks() {{{
        global $context, $settings;

        $db = Database::getInstance();

        $context['tp_panels'] = array();
        if(isset($_COOKIE['tp_panels'])){
            $shrinks = explode(',', $_COOKIE['tp_panels']);
            foreach($shrinks as $sh => $val) {
                $context['tp_panels'][] = $val;
            }
        }

        // the generic panel upshrink code
        $context['html_headers'] .= '
          <script type="text/javascript"><!-- // --><![CDATA[
            ' . (count($context['tp_panels']) > 0 ? '
            var tpPanels = new Array(\'' . (implode("','",$context['tp_panels'])) . '\');' : '
            var tpPanels = new Array();') . '
            function togglepanel( targetID )
            {
                var pstate = 0;
                var panel = targetID;
                var img = "toggle_" + targetID;
                var ap = 0;

                if ( document.getElementById && (0 !== panel.length) ) {
                    target = document.getElementById( panel );
                    if ( target !== null ) {
                        if ( target.style.display == "none" ) {
                            target.style.display = "";
                            pstate = 1;
                            removeFromArray(targetID, tpPanels);
                            document.cookie="tp_panels=" + tpPanels.join(",") + "; expires=Wednesday, 01-Aug-2040 08:00:00 GMT";
                            var image = document.getElementById(img);
                            if(image !== null) {
                                image.src = \'' . $settings['tp_images_url'] . '/TPupshrink.png\';
                            }
                        }
                        else {
                            target.style.display = "none";
                            pstate = 0;
                            tpPanels.push(targetID);
                            document.cookie="tp_panels=" + tpPanels.join(",") + "; expires=Wednesday, 01-Aug-2040 08:00:00 GMT";
                            var image = document.getElementById(img);
                            if(image !== null) {
                                image.src = \'' . $settings['tp_images_url'] . '/TPupshrink2.png\';
                            }
                        }
                    }
                }
            }
            function removeFromArray(value, array){
                for(var x=0;x<array.length;x++){
                    if(array[x]==value){
                        array.splice(x, 1);
                    }
                }
                return array;
            }
            function inArray(value, array){
                for(var x=0;x<array.length;x++){
                    if(array[x]==value){
                        return 1;
                    }
                }
                return 0;
            }
        // ]]></script>';

        $panels = array('Left', 'Right', 'Top', 'Center', 'Lower', 'Bottom');
        $context['TPortal']['upshrinkpanel'] = '';

        if($context['TPortal']['showcollapse'] == 1) {
            foreach($panels as $pa => $pan) {
                $side = strtolower($pan);
                if($context['TPortal'][$side.'panel'] == 1) {
                    // add to the panel
                    if($pan == 'Left' || $pan == 'Right') {
                        $context['TPortal']['upshrinkpanel'] .= $this->hidePanelTitle('tp' . strtolower($pan) . 'barHeader', 'tp' . strtolower($pan) . 'barContainer', strtolower($pan).'-tp-upshrink_description');
                    }
                    else {
                        $context['TPortal']['upshrinkpanel'] .= $this->hidePanelTitle('tp' . strtolower($pan) . 'barHeader', '', strtolower($pan).'-tp-upshrink_description');
                    }
                }
            }
        }

        // get user values
        if($context['user']['is_logged']) {
            // set some values based on user-prefs
            $result = $db->query('', '
                SELECT type, value, item
                FROM {db_prefix}tp_data
                WHERE type = {int:type}
                AND id_member = {int:id_mem}',
                array('type' => 2, 'id_mem' => $context['user']['id'])
            );

            if($db->num_rows($result) > 0) {
                while($row = $db->fetch_assoc($result)) {
                    $context['TPortal']['usersettings']['wysiwyg'] = $row['value'];
                }
                $db->free_result($result);
            }
            $context['TPortal']['use_wysiwyg']  = (int) $context['TPortal']['use_wysiwyg'];
            $context['TPortal']['show_wysiwyg'] = $context['TPortal']['use_wysiwyg'];

            if ($context['TPortal']['use_wysiwyg'] > 0) {
                $context['TPortal']['allow_wysiwyg'] = true;
                if (isset($context['TPortal']['usersettings']['wysiwyg'])) {
                    $context['TPortal']['show_wysiwyg'] = (int) $context['TPortal']['usersettings']['wysiwyg'];
                }
            }
            else {
                $context['TPortal']['show_wysiwyg'] = $context['TPortal']['use_wysiwyg'];
                $context['TPortal']['allow_wysiwyg'] = false;
            }

            // check that we are not in admin section
            if((isset($_GET['action']) && $_GET['action'] == 'tpadmin') && ((isset($_GET['sa']) && $_GET['sa'] == 'settings') || !isset($_GET['sa']))) {
                $in_admin = true;
            }
        }

        // get the cookie for upshrinks
        $context['TPortal']['upshrinkblocks'] = array();
        if(isset($_COOKIE['tp-upshrinks'])) {
            $shrinks = explode(',', $_COOKIE['tp-upshrinks']);
            foreach($shrinks as $sh => $val) {
                $context['TPortal']['upshrinkblocks'][] = $val;
            }
        }

        return;

    }}}

    public function blockGrid($block, $theme, $pos, $side, $last = false, $gridtype, $none = false) {{{
        global $context;

        // first, set the table, equal in all grids
        if($pos == 0) {
            echo '<div style="width:100%;">';
        }

        if(isset($context['TPortal']['grid'][$gridtype][$pos]['doubleheight'])) {
            $dh = true;
        }
        else {
            $dh = false;
        }

        // render if its not empty
        if($none == false) {
            echo $context['TPortal']['grid'][$gridtype][$pos]['before'] , call_user_func($context['TPortal']['hooks']['tp_block'], $block, $theme, $side, $dh) , $context['TPortal']['grid'][$gridtype][$pos]['after'];
        }
        else {
            echo $context['TPortal']['grid'][$gridtype][$pos]['before'] . '&nbsp;' . $context['TPortal']['grid'][$gridtype][$pos]['after'];
        }

        // last..if its the last block,close the table
        if($last) {
            echo '<p class="clearthefloat"></p></div>';
        }

    }}}

    public function blockGrids() {{{
        global $context;

        $context['TPortal']['grid'] = array();
        $context['TPortal']['grid']['colspan3'][0] = array('before' => '<div class="gridColumns">', 'after' => '</div>');
        $context['TPortal']['grid']['colspan3'][1] = array('before' => '<div><div class="gridColumns" style="width:32.3%;padding-right:0.7%;float:left;">', 'after' => '</div>');
        $context['TPortal']['grid']['colspan3'][2] = array('before' => '<div class="gridColumns" style="width:32.3%;padding-right:0.7%;float:left;">', 'after' => '</div>');
        $context['TPortal']['grid']['colspan3'][3] = array('before' => '<div class="gridColumns" style="width:34%;float:left;">', 'after' => '</div><p class="clearthefloat"></p></div>');

        $context['TPortal']['grid']['rowspan1'][0] = array('before' => '<div class="gridC" style="width:32.3%;padding-right: 0.7%;float:left;">', 'after' => '</div>', 'doubleheight' => true);
        $context['TPortal']['grid']['rowspan1'][1] = array('before' => '<div class="gridC" style="width:67%;float:left;"><div class="gridColumns" style="width:49%;padding-right: 1%;padding-bottom: 5px;float:left;">', 'after' => '</div>');
        $context['TPortal']['grid']['rowspan1'][2] = array('before' => '<div class="gridColumns" style="width:50%;float:left;">', 'after' => '</div>');
        $context['TPortal']['grid']['rowspan1'][3] = array('before' => '<div class="gridColumns" style="width:49%;padding-right: 1%;float:left;">', 'after' => '</div>');
        $context['TPortal']['grid']['rowspan1'][4] = array('before' => '<div class="gridColumns" style="width:50%;float:left;">', 'after' => '</div><p class="clearthefloat"></p></div>');

    }}}

    // TPortal leftblocks
    public function TPortal_leftbar() {{{
        TPortal_sidebar('left');
    }}}

    // TPortal centerbar
    public function TPortal_centerbar() {{{
        TPortal_sidebar('center');
    }}}

    // TPortal rightbar
    public function TPortal_rightbar() {{{
        TPortal_sidebar('right');
    }}}

    public function collectSnippets() {{{
        global $context;

        // fetch any blockcodes in blockcodes folder
        $codefiles = array();
        if ($handle = opendir($context['TPortal']['blockcode_upload_path'])) {
            while (false !== ($file = readdir($handle))) {
                if($file != '.' && $file != '..' && $file != '.htaccess' && substr($file, (strlen($file) - 10), 10) == '.blockcode') {
                    $snippet = self::parseModfile(file_get_contents($context['TPortal']['blockcode_upload_path'] . $file), array('name', 'author', 'version', 'date', 'description'));
                    $codefiles[] = array(
                        'file' => substr($file, 0, strlen($file) - 10),
                        'name' => isset($snippet['name']) ? $snippet['name'] : '',
                        'author' => isset($snippet['author']) ? $snippet['author'] : '',
                        'text' => isset($snippet['description']) ? $snippet['description'] : '',
                    );
                }
            }
            sort($codefiles);
            closedir($handle);
        }
        return $codefiles;

    }}}

    public function parseModfile($file , $returnarray) {{{
        $file = strtr($file, array("\r" => ''));
        $snippet = array();

        while (preg_match('~<(name|code|parameter|author|version|date|description)>\n(.*?)\n</\\1>~is', $file, $code_match) != 0)
        {
            // get the title of this snippet
            if ($code_match[1] == 'name' && in_array('name', $returnarray))
                $snippet['name'] = $code_match[2];
            elseif ($code_match[1] == 'code' && in_array('code', $returnarray))
                $snippet['code'] = $code_match[2];
            elseif ($code_match[1] == 'parameter' && in_array('name', $returnarray))
                $snippet['parameter'][] = $code_match[2];
            elseif ($code_match[1] == 'author' && in_array('author', $returnarray))
                $snippet['author'] = $code_match[2];
            elseif ($code_match[1] == 'version' && in_array('version', $returnarray))
                $snippet['version'] = $code_match[2];
            elseif ($code_match[1] == 'date' && in_array('date', $returnarray))
                $snippet['date'] = $code_match[2];
            elseif ($code_match[1] == 'description' && in_array('description', $returnarray))
                $snippet['description'] = $code_match[2];

            // Get rid of the old tag.
            $file = substr_replace($file, '', strpos($file, $code_match[0]), strlen($code_match[0]));
        }
        return $snippet;

    }}}

    public function ArticleCategories($use_sorted = false) {{{
        global $context, $txt;

        $db = Database::getInstance();

        $context['TPortal']['catnames'] = array();
        $context['TPortal']['categories_shortname'] = array();

        //first : fetch all allowed categories
        $sorted = array();
        // for root category

        $sorted[9999] = array(
            'id' => 9999,
            'name' => '&laquo;' . $txt['tp-noname'] . '&raquo;',
            'parent' => '0',
            'access' => '-1, 0, 1',
            'indent' => 1,
        );
        $total = array();
        $request2 =  $db->query('', '
            SELECT category, COUNT(*) as files
            FROM {db_prefix}tp_articles
            WHERE category > {int:category} GROUP BY category',
            array(
                'category' => 0
            )
        );
        if($db->num_rows($request2) > 0)
        {
            while($row = $db->fetch_assoc($request2))
            {
                $total[$row['category']] = $row['files'];
            }
            $db->free_result($request2);
        }
        $total2 = array();
        $request2 =  $db->query('', '
            SELECT parent, COUNT(*) as siblings
            FROM {db_prefix}tp_categories
            WHERE item_type = {string:type} GROUP BY parent',
            array(
                'type' => 'category'
            )
        );
        if($db->num_rows($request2) > 0)
        {
            while($row = $db->fetch_assoc($request2))
            {
                $total2[$row['parent']] = $row['siblings'];
            }
            $db->free_result($request2);
        }

        $request =  $db->query('', '
            SELECT cats.*
            FROM {db_prefix}tp_categories as cats
            WHERE cats.item_type = {string:type}
            ORDER BY cats.display_name ASC',
            array(
                'type' => 'category'
            )
        );

        if($db->num_rows($request) > 0)
        {
            while ($row = $db->fetch_assoc($request))
            {
                // set the options up
                $options = array(
                    'layout' => '1',
                    'width' => '100%',
                    'cols' => '1',
                    'sort' => 'date',
                    'sortorder' => 'desc',
                    'showchild' => '1',
                    'articlecount' => '5',
                    'catlayout' => '1',
                    'leftpanel' => '0',
                    'rightpanel' => '0',
                    'toppanel' => '0' ,
                    'bottompanel' => '0' ,
                    'upperpanel' => '0' ,
                    'lowerpanel' => '0',
                );
                $opts = explode('|' , $row['settings']);
                foreach($opts as $op => $val)
                {
                    if(substr($val,0,7) == 'layout=')
                        $options['layout'] = substr($val,7);
                    elseif(substr($val,0,6) == 'width=')
                        $options['width'] = substr($val,6);
                    elseif(substr($val,0,5) == 'cols=')
                        $options['cols'] = substr($val,5);
                    elseif(substr($val,0,5) == 'sort=')
                        $options['sort'] = substr($val,5);
                    elseif(substr($val,0,10) == 'sortorder=')
                        $options['sortorder'] = substr($val,10);
                    elseif(substr($val,0,10) == 'showchild=')
                        $options['showchild'] = substr($val,10);
                    elseif(substr($val,0,13) == 'articlecount=')
                        $options['articlecount'] = substr($val,13);
                    elseif(substr($val,0,10) == 'catlayout=')
                        $options['catlayout'] = substr($val,10);
                    elseif(substr($val,0,10) == 'leftpanel=')
                        $options['leftpanel'] = substr($val,10);
                    elseif(substr($val,0,11) == 'rightpanel=')
                        $options['rightpanel'] = substr($val,11);
                    elseif(substr($val,0,9) == 'toppanel=')
                        $options['toppanel'] = substr($val,9);
                    elseif(substr($val,0,12) == 'bottompanel=')
                        $options['bottompanel'] = substr($val,12);
                    elseif(substr($val,0,11) == 'upperpanel=')
                        $options['centerpanel'] = substr($val,11);
                    elseif(substr($val,0,11) == 'lowerpanel=')
                        $options['lowerpanel'] = substr($val,11);
                }

                // check the parent
                if($row['parent'] == $row['id'] || $row['parent'] == '' || $row['parent'] == '0')
                    $row['parent'] = 9999;
                // check access
                $show = self::perm($row['access']);
                if($show) {
                    $sorted[$row['id']] = array(
                        'id' => $row['id'],
                        'shortname' => !empty($row['short_name']) ? $row['short_name'] : $row['id'],
                        'name' => $row['display_name'],
                        'parent' => $row['parent'],
                        'access' => $row['access'],
                        'icon' => $row['dt_log'],
                        'totalfiles' => !empty($total[$row['id']][0]) ? $total[$row['id']][0] : 0,
                        'children' => !empty($total2[$row['id']][0]) ? $total2[$row['id']][0] : 0,
                        'options' => array(
                            'layout' => $options['layout'],
                            'catlayout' => $options['catlayout'],
                            'width' => $options['width'],
                            'cols' => $options['cols'],
                            'sort' => $options['sort'],
                            'sortorder' => $options['sortorder'],
                            'showchild' => $options['showchild'],
                            'articlecount' => $options['articlecount'],
                            'leftpanel' => $options['leftpanel'],
                            'rightpanel' => $options['rightpanel'],
                            'toppanel' => $options['toppanel'],
                            'bottompanel' => $options['bottompanel'],
                            'upperpanel' => $options['upperpanel'],
                            'lowerpanel' => $options['lowerpanel'],
                        ),
                    );
                    $context['TPortal']['catnames'][$row['id']]=$row['display_name'];
                    $context['TPortal']['categories_shortname'][$sorted[$row['id']]['shortname']]=$row['id'];
                }
            }
            $db->free_result($request);
        }
        $context['TPortal']['article_categories'] = array();
        if($use_sorted) {
            // sort them
            if(count($sorted)>1) {
                $context['TPortal']['article_categories'] = self::chain('id', 'parent', 'name', $sorted);
            }
            else {
                $context['TPortal']['article_categories'] = $sorted;
            }
            unset($context['TPortal']['article_categories'][0]);
        }
        else {
            $context['TPortal']['article_categories'] = $sorted;
            unset($context['TPortal']['article_categories'][0]);
        }
    }}}

    public function chain($primary_field, $parent_field, $sort_field, $rows, $root_id = 0, $maxlevel = 25) {{{
       $c = new Chain($primary_field, $parent_field, $sort_field, $rows, $root_id, $maxlevel);
       return $c->chain_table;
    }}}


    public function get_snippets_xml() {{{
        return;
    }}}

    public function TP_createtopic($title, $text, $icon, $board, $sticky = 0, $submitter) {{{
        global $user_info, $board_info, $sourcedir;

        require_once(SUBSDIR.'/Post.subs.php');

        $body = str_replace(array("<",">","\n","	"), array("&lt;","&gt;","<br>","&nbsp;"), $text);
        preparsecode($body);

        // Collect all parameters for the creation or modification of a post.
        $msgOptions = array(
            'id' => empty($_REQUEST['msg']) ? 0 : (int) $_REQUEST['msg'],
            'subject' => $title,
            'body' =>$body,
            'icon' => $icon,
            'smileys_enabled' => '1',
            'attachments' => array(),
        );
        $topicOptions = array(
            'id' => empty($topic) ? 0 : $topic,
            'board' => $board,
            'poll' => null,
            'lock_mode' => null,
            'sticky_mode' => $sticky,
            'mark_as_read' => true,
        );
        $posterOptions = array(
            'id' => $submitter,
            'name' => '',
            'email' => '',
            'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']) && isset($board_info['posts_count']),
        );

        if(createPost($msgOptions, $topicOptions, $posterOptions))
            $topi = $topicOptions['id'];
        else
            $topi = 0;

        return $topi;
    }}}

    public function wysiwygSetup( $id, $body = '' ) {{{
        global $context, $boardurl, $txt;

        $context['html_headers'] .= '
            <link rel="stylesheet" href="'.$boardurl.'/TinyPortal/Views/scripts/sceditor/minified/themes/default.min.css" />
            <script src="'.$boardurl.'/TinyPortal/Views/scripts/sceditor/minified/sceditor.min.js"></script>
            <script src="'.$boardurl.'/TinyPortal/Views/scripts/sceditor/minified/formats/xhtml.js"></script>
            <script src="'.$boardurl.'/TinyPortal/Views/scripts/sceditor/languages/'.$txt['lang_dictionary'].'.js"></script>
            <style>
                .sceditor-button-floatleft div { background: url('.$boardurl.'/TinyPortal/Views/images/floatleft.png); width:24px; height:24px; margin: -3px; }
                .sceditor-button-floatright div { background: url('.$boardurl.'/TinyPortal/Views/images/floatright.png); width:24px; height:24px; margin: -3px; }
            </style>
            <script type="text/javascript"><!-- // --><![CDATA[
                sceditor.command.set(\'floatleft\', {
                    exec: function() {
                        // this is set to the editor instance
                        this.wysiwygEditorInsertHtml(\'<div style="float:left;">\', \'</div>\');
                    },
                    txtExec: [\'<div style="float:left;">\', \'</div>\'],
                    tooltip: \''.$txt['editor_tp_floatleft'].'\'
                });
                sceditor.command.set(\'floatright\', {
                    exec: function() {
                        // this is set to the editor instance
                        this.wysiwygEditorInsertHtml(\'<div style="float:right;">\', \'</div>\');
                    },
                    txtExec: [\'<div style="float:right;">\', \'</div>\'],
                    tooltip: \''.$txt['editor_tp_floatright'].'\'
                });

                sceditor.command.set( \'youtube\', {
                    exec: function (caller) {
                        var editor = this;
                        editor.commands.youtube._dropDown(editor, caller, function (id, time) {
                            editor.insert(\'<div class="youtubecontainer"><iframe allowfullscreen src="https://www.youtube.com/embed/\' + id + \'?wmode=opaque&start=\' + time + \'" data-youtube-id="\' + id + \'"></iframe></div>&nbsp;\');
                        });
                    },
                    txtExec: function (caller) {
                        var editor = this;
                        editor.commands.youtube._dropDown(editor, caller, function (id, time) {
                            editor.insert(\'<div class="youtubecontainer"><iframe allowfullscreen src="https://www.youtube.com/embed/\' + id + \'?wmode=opaque&start=\' + time + \'" data-youtube-id="\' + id + \'"></iframe></div>&nbsp;\');
                        });
                    },
                });
            // ]]></script>';

        if($context['TPortal']['use_dragdrop']) {
            $context['html_headers'] .= '<script src="'.$boardurl.'/TinyPortal/Views/scripts/sceditor/minified/plugins/dragdrop.js"></script>';
        }

    }}}

    public function wysiwyg($textarea, $body, $upload = true, $uploadname, $use = 1, $showchoice = true) {{{
        global $context, $scripturl, $txt, $boardurl, $user_info;

        echo '
        <div style="padding-top: 10px;">
            <textarea style="width: 100%; height: ' . $context['TPortal']['editorheight'] . 'px;" name="'.$textarea.'" id="'.$textarea.'">'.$body.'</textarea>';

        if($context['TPortal']['use_dragdrop']) {
            echo '<script type="text/javascript"><!-- // --><![CDATA[
                function tpImageUpload(file) {
                    var form = new FormData();
                    form.append(\'image\', file);
                    return fetch(\''.$scripturl.'?action=admin;area=tparticles;sa=uploadimage\', {
                        method: \'post\',
                        credentials: \'same-origin\',
                        body: form,
                        dataType : \'json\',
                    }).then(function (res) {
                        return res.json();
                    }).then(function(result) {
                        if (result.success) {
                            return result.data;
                        }
                        throw \'Upload error\';
                    });
                }

                var dragdropOptions = {
                    // The allowed mime types that can be dropped on the editor
                    allowedTypes: [\'image/gif\', \'image/jpeg\', \'image/png\'],
                    handleFile: function (file, createPlaceholder) {
                    var placeholder = createPlaceholder();

                    tpImageUpload(file).then(function (url) {
                        // Replace the placeholder with the image HTML
                        placeholder.insert(\'<img src=\' + url + \' />\');
                    }).catch(function () {
                        // Error so remove the placeholder
                        placeholder.cancel();

                        alert(\'Problem uploading image.\');
                    });
                    }
                };
                // ]]></script>';
        }

        echo '	<script type="text/javascript"><!-- // --><![CDATA[
                var textarea = document.getElementById(\''.$textarea.'\');
                sceditor.create(textarea, {';
            if($context['TPortal']['use_dragdrop']) {
                echo'
                    // Enable the drag and drop plugin
                    plugins: \'dragdrop\',
                    // Set the drag and drop plugin options
                    dragdrop: dragdropOptions,';
            }

        echo '
                    toolbar: \'bold,italic,underline,strike,subscript,superscript|left,center,right,justify|font,size,color,removeformat|cut,copy,paste|bulletlist,orderedlist,indent,outdent|table|code,quote|horizontalrule,image,email,link,unlink|emoticon,youtube,date,time|ltr,rtl|print,maximize,source|floatleft,floatright\',';
            echo '
                    format: \'xhtml\',
                    locale: "' . $txt['lang_dictionary'] . '",
                    style: \''.$boardurl.'/TinyPortal/Views/scripts/sceditor/minified/themes/content/default.min.css\',
                    emoticonsRoot: \''.$boardurl.'/TinyPortal/Views/scripts/sceditor/\'
                });

            // ]]></script>';


        // only if you can edit your own articles
        if($upload && allowedTo('tp_editownarticle')) {
            // fetch all images you have uploaded
            $imgfiles = array();
            if ($handle = opendir($context['TPortal']['image_upload_path'].'thumbs')) {
                while (false !== ($file = readdir($handle))) {
                    if($file != '.' && $file !='..' && $file !='.htaccess' && substr($file, 0, strlen($user_info['id']) + 9) == 'thumb_'.$user_info['id'].'uid') {
                        $imgfiles[($context['TPortal']['image_upload_path'].'thumbs/'.$file)] = $file;
                    }
                }
                closedir($handle);
                ksort($imgfiles);
                $imgs = $imgfiles;
            }
            echo '
            <br><div class="title_bar"><h3 class="category_header">' , $txt['tp-quicklist'] , '</h3></div>
            <div class="content smalltext tp_pad">' , $txt['tp-quicklist2'] , '</div>
            <div class="content tpquicklist">
            <div class="tpthumb">';
            if(isset($imgs)) {
                foreach($imgs as $im) {
                    echo '<img src="', str_replace(BOARDDIR, $boardurl, $context['TPortal']['image_upload_path']), substr($im,6) , '"  alt="'.substr($im,6).'" title="'.substr($im,6).'" />';
                }
            }

            echo '
            </div>
            </div>
            <div class="tp_pad">' , $txt['tp-uploadfile'] ,'<input type="file" name="'.$uploadname.'"></div>
        </div>';
        }

    }}}

    public function hidePanel($id, $inline = false, $string = false, $margin='') {{{
        global $context, $settings;

        $what = '
        <a style="' . (!$inline ? 'float: right;' : '') . ' cursor: pointer;" onclick="togglepanel(\''.$id.'\')">
            <img id="toggle_' . $id . '" src="' . $settings['tp_images_url'] . '/TPupshrink' . (in_array($id, $context['tp_panels']) ? '2' : '') . '.png" ' . (!empty($margin) ? 'style="margin: '.$margin.';"' : '') . 'alt="*" />
        </a>';

        if($string) {
            return $what;
        }
        else {
            echo $what;
        }

    }}}

    public function hidePanelTitle($id, $id2, $alt) {{{
        global $txt, $context, $settings;

        $what = '
        <a title="'.$txt[$alt].'" style="cursor: pointer;" onclick="togglepanel(\''.$id.'\');togglepanel(\''.$id2.'\')">
            <img id="toggle_' . $id . '" src="' . $settings['tp_images_url'] . '/TPupshrink' . (in_array($id, $context['tp_panels']) ? '2' : '') . '.png" alt="*" />
        </a>';

        return $what;
    }}}

    public function perm($perm, $moderate = '') {{{
        return Permissions::getInstance()->getPermissions($perm, $moderate);
    }}}

    public function tpsort($a, $b) {{{
        return strnatcasecmp($b["timestamp"], $a["timestamp"]);
    }}}

    // add to the linktree
    public function addLinkTree($url, $name) {{{
        global $context;

        $context['linktree'][] = array('url' => $url, 'name' => $name);
    }}}

    // strip the linktree
    public function strip_linktree() {{{
        global $context, $scripturl;

        $context['linktree'] = array();
        $context['linktree'][] = array('url' => $scripturl, 'name' => $context['forum_name']);
    }}}

    // Constructs a page list.
    public function pageIndex($base_url, &$start, $max_value, $num_per_page) {{{
        global $modSettings, $txt;

        $flexible_start = false;
        // Save whether $start was less than 0 or not.
        $start_invalid = $start < 0;

        // Make sure $start is a proper variable - not less than 0.
        if ($start_invalid)
            $start = 0;
        // Not greater than the upper bound.
        elseif ($start >= $max_value)
            $start = max(0, (int) $max_value - (((int) $max_value % (int) $num_per_page) == 0 ? $num_per_page : ((int) $max_value % (int) $num_per_page)));
        // And it has to be a multiple of $num_per_page!
        else
            $start = max(0, (int) $start - ((int) $start % (int) $num_per_page));

        // Wireless will need the protocol on the URL somewhere.
        if (defined('WIRELESS') && WIRELESS )
            $base_url .= ';' . WIRELESS_PROTOCOL;

        $base_link = '<a class="navPages" href="' . ($flexible_start ? $base_url : strtr($base_url, array('%' => '%%')) . ';p=%d') . '">%s</a> ';

        // Compact pages is off or on?
        if (empty($modSettings['compactTopicPagesEnable']))
        {
            // Show the left arrow.
            $pageindex = $start == 0 ? ' ' : sprintf($base_link, $start - $num_per_page, '&#171;');

            // Show all the pages.
            $display_page = 1;
            for ($counter = 0; $counter < $max_value; $counter += $num_per_page)
                $pageindex .= $start == $counter && !$start_invalid ? '<b>' . $display_page++ . '</b> ' : sprintf($base_link, $counter, $display_page++);

            // Show the right arrow.
            $display_page = ($start + $num_per_page) > $max_value ? $max_value : ($start + $num_per_page);
            if ($start != $counter - $max_value && !$start_invalid)
                $pageindex .= $display_page > $counter - $num_per_page ? ' ' : sprintf($base_link, $display_page, '&#187;');
        }
        else
        {
            // If they didn't enter an odd value, pretend they did.
            $PageContiguous = (int) ($modSettings['compactTopicPagesContiguous'] - ($modSettings['compactTopicPagesContiguous'] % 2)) / 2;

            // Show the first page. (>1< ... 6 7 [8] 9 10 ... 15)
            if ($start > $num_per_page * $PageContiguous)
                $pageindex = sprintf($base_link, 0, '1');
            else
                $pageindex = '';

            // Show the ... after the first page.  (1 >...< 6 7 [8] 9 10 ... 15)
            if ($start > $num_per_page * ($PageContiguous + 1))
                $pageindex .= '<b> ... </b>';

            // Show the pages before the current one. (1 ... >6 7< [8] 9 10 ... 15)
            for ($nCont = $PageContiguous; $nCont >= 1; $nCont--)
                if ($start >= $num_per_page * $nCont)
                {
                    $tmpStart = $start - $num_per_page * $nCont;
                    $pageindex .= sprintf($base_link, $tmpStart, $tmpStart / $num_per_page + 1);
                }

            // Show the current page. (1 ... 6 7 >[8]< 9 10 ... 15)
            if (!$start_invalid)
                $pageindex .= '[<b>' . ($start / $num_per_page + 1) . '</b>] ';
            else
                $pageindex .= sprintf($base_link, $start, $start / $num_per_page + 1);

            // Show the pages after the current one... (1 ... 6 7 [8] >9 10< ... 15)
            $tmpMaxPages = (int) (($max_value - 1) / $num_per_page) * $num_per_page;
            for ($nCont = 1; $nCont <= $PageContiguous; $nCont++)
                if ($start + $num_per_page * $nCont <= $tmpMaxPages)
                {
                    $tmpStart = $start + $num_per_page * $nCont;
                    $pageindex .= sprintf($base_link, $tmpStart, $tmpStart / $num_per_page + 1);
                }

            // Show the '...' part near the end. (1 ... 6 7 [8] 9 10 >...< 15)
            if ($start + $num_per_page * ($PageContiguous + 1) < $tmpMaxPages)
                $pageindex .= '<b> ... </b>';

            // Show the last number in the list. (1 ... 6 7 [8] 9 10 ... >15<)
            if ($start + $num_per_page * $PageContiguous < $tmpMaxPages)
                $pageindex .= sprintf($base_link, $tmpMaxPages, $tmpMaxPages / $num_per_page + 1);
        }
        $pageindex = $txt['pages']. ': ' . $pageindex;
        return $pageindex;
    }}}

    public function renderArticle($intro = '') {{{
        global $context, $txt, $scripturl;
        global $image_proxy_enabled, $image_proxy_secret, $boardurl;

        $data = '';

        // just return if data is missing
        if(!isset($context['TPortal']['article'])) {
            return;
        }

        $data .= '
        <div class="article_inner">';
        // use intro!
        if(($context['TPortal']['article']['useintro'] == '1' && !$context['TPortal']['single_article']) || !empty($intro)) {
            if($context['TPortal']['article']['rendertype'] == 'php') {
                ob_start();
                eval(self::convertphp($context['TPortal']['article']['intro'], true));
                $data .= ob_get_clean();
            }
            elseif($context['TPortal']['article']['rendertype'] == 'bbc' || $context['TPortal']['article']['rendertype'] == 'import') {
                if(Util::isHTML($context['TPortal']['article']['intro']) || isset($context['TPortal']['article']['parsed_bbc'])) {
                    $data .= $context['TPortal']['article']['intro'];
                }
                else {
                    $data .= $this->parse_bbc($context['TPortal']['article']['intro']);
                }
            }
            else {
                $data .= $context['TPortal']['article']['intro'];
            }
            $data .= '<p class="tp_readmore"><b><a href="' .$scripturl . '?page='. ( !empty($context['TPortal']['article']['shortname']) ? $context['TPortal']['article']['shortname'] : $context['TPortal']['article']['id'] ) . '' . (( defined('WIRELESS') && WIRELESS ) ? ';' . WIRELESS_PROTOCOL : '' ). '">'.$txt['tp-readmore'].'</a></b></p>';
        }
        else {
            if($context['TPortal']['article']['rendertype'] == 'php') {
                ob_start();
                eval(self::convertphp($context['TPortal']['article']['body'], true));
                $data .= ob_get_clean();
            }
            elseif($context['TPortal']['article']['rendertype'] == 'bbc') {
                if(Util::isHTML($context['TPortal']['article']['body']) || isset($context['TPortal']['article']['parsed_bbc'])) {
                    $data .= $context['TPortal']['article']['body'];
                }
                else {
                    $data .= $this->parse_bbc($context['TPortal']['article']['body']);
                }

                if(!empty($context['TPortal']['article']['readmore'])) {
                    $data .= $context['TPortal']['article']['readmore'];
                }
            }
            elseif($context['TPortal']['article']['rendertype'] == 'import') {
                if(!file_exists(BOARDDIR. '/' . $context['TPortal']['article']['fileimport'])) {
                    $data .= '<em>' . $txt['tp-cannotfetchfile'] . '</em>';
                }
                else {
                    include($context['TPortal']['article']['fileimport']);
                }
            }
            else {
                $post = $context['TPortal']['article']['body'];
                if ($image_proxy_enabled && !empty($post) && stripos($post, 'http://') !== false) {
                    $post = preg_replace_callback("~<img([\w\W]+?)/>~",
                        function( $matches ) use ( $boardurl, $image_proxy_secret ) {
                            if (stripos($matches[0], 'http://') !== false) {
                                $matches[0] = preg_replace_callback("~src\=(?:\"|\')(.+?)(?:\"|\')~",
                                    function( $src ) use ( $boardurl, $image_proxy_secret ) {
                                        if (stripos($src[1], 'http://') !== false)
                                            return ' src="'. $boardurl . '/proxy.php?request='.urlencode($src[1]).'&hash=' . md5($src[1] . $image_proxy_secret) .'"';
                                        else
                                            return $src[0];
                                    },
                                    $matches[0]);
                            }
                            return $matches[0];
                        },
                    $post);
                }
                $data .= $post;
            }
        }
        $data .= '</div> <!-- article_inner -->';
        return $data;
    }}}

    public function renderBlockArticle() {{{

        global $context, $txt;

        // just return if data is missing
        if(!isset($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']])) {
            return;
        }

        echo '
            <div class="article_inner">';
        if($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['rendertype'] == 'php') {
            eval($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['body']);
        }
        elseif($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['rendertype'] == 'import') {
            if(!file_exists(BOARDDIR. '/' . $context['TPortal']['blockarticles'][$context['TPortals']['blockarticle']]['fileimport'])) {
                echo '<em>' , $txt['tp-cannotfetchfile'] , '</em>';
            }
            else {
                include($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['fileimport']);
            }
        }
        elseif($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['rendertype']=='bbc') {
            echo \BBC\ParserWrapper::getInstance()->parseMessage($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['body']);
        }
        else {
            echo $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['body'];
        }
    
        echo '
            </div>';

        return;

    }}}

    public function render_template($code, $render = true) {{{
        global $context;

        if(!empty($context['TPortal']['disable_template_eval']) && $render == true) {
            if(preg_match_all('~(?<={)([A-Za-z_]+)(?=})~', $code, $match) !== false) {
                foreach($match[0] as $func) {
                    if(function_exists($func)) {
                        $output = $func(false);
                        $code   = str_replace( '{'.$func.'}', $output, $code);
                    }
                }
                echo $code;
            }
        }
        else {
            $ncode = 'echo \'' . str_replace(array('{','}'),array("', ","(), '"),$code).'\';';
            if($render) {
                eval($ncode);
            }
            else {
                return $ncode;
            }
        }
    }}}

    public function render_template_layout($code, $prefix = '') {{{
        global $context;

        if(!empty($context['TPortal']['disable_template_eval'])) {
            if(preg_match_all('~(?<={)([A-Za-z0-9]+)(?=})~', $code, $match) !== false) {
                foreach($match[0] as $suffix) {
                    $func = (string)"$prefix$suffix";
                    if(is_callable(array($this, $func), true, $funcName)) {
                        ob_start();
                        call_user_func(array($this, $func));
                        $output = ob_get_clean();
                        $code   = str_replace( '{'.$suffix.'}', $output, $code);
                    }
                }
                echo $code;
            }
        }
        else {
            $ncode = 'echo \'' . str_replace(array('{','}'),array("', " . $prefix , "(), '"),$code).'\';';
            eval($ncode);
        }
    }}}

    public function hidebars($what = 'all') {{{
        global $context;

        if($what == 'all'){
            $context['TPortal']['leftpanel'] = 0;
            $context['TPortal']['centerpanel'] = 0;
            $context['TPortal']['rightpanel'] = 0;
            $context['TPortal']['bottompanel'] = 0;
            $context['TPortal']['toppanel'] = 0;
            $context['TPortal']['lowerpanel'] = 0;
        }
        elseif($what == 'left')
            $context['TPortal']['leftpanel'] = 0;
        elseif($what=='right')
            $context['TPortal']['rightpanel'] = 0;
        elseif($what=='center')
            $context['TPortal']['centerpanel'] = 0;
        elseif($what=='bottom')
            $context['TPortal']['bottompanel'] = 0;
        elseif($what=='top')
            $context['TPortal']['toppanel'] = 0;
        elseif($what=='lower')
            $context['TPortal']['lowerpanel'] = 0;
    }}}

    public function getlangOption($langlist, $set) {{{

        $lang   = explode("|", $langlist);
        if(is_countable($lang)) {
            $num = count($lang);
        }
        else {
            $num = 0;
        }

        $setlang = '';

        for($i=0; $i < $num ; $i = $i + 2){
            if($lang[$i] == $set)
                $setlang = $lang[$i+1];
        }

        return $setlang;
    }}}

    public function category_col($column, $featured = false, $render = true) {{{
        global $context;

        unset($context['TPortal']['article']);

        if(!isset($context['TPortal']['category'][$column])) {
            return;
        }

        if($column == 'featured' ) {
            $context['TPortal']['category']['featured'] = array( $context['TPortal']['category']['featured'] );
        }

        foreach($context['TPortal']['category'][$column] as $article => $context['TPortal']['article']) {
            if(!empty($context['TPortal']['article']['template'])) {
                $this->render_template($context['TPortal']['article']['template'], $render);
            }
            else {
                $this->render_template(article_renders($context['TPortal']['category']['options']['catlayout'], false, $featured), $render);
            }
            unset($context['TPortal']['article']);
        }
    }}}

    // the featured or first article
    public function category_featured( $render = true) {{{
        return $this->category_col('featured', true, $render);
    }}}

    // the first half
    public function category_col1($render = true) {{{
        return $this->category_col('col1', false, $render);
    }}}

    // the second half
    public function category_col2($render = true) {{{
        return $this->category_col('col2', false, $render);
    }}}

    public function parseRSS($override = '', $encoding = 0, $max = 10) {{{
        global $context;

        // Initialise the number of RSS Feeds to show
        $numShown = 0;

        $backend = isset($context['TPortal']['rss']) ? $context['TPortal']['rss'] : '';
        if($override != '')
            $backend = $override;
        
        require_once(SUBSDIR . '/Package.subs.php');
		$data   = fetch_web_data($backend);
        $xml    = simplexml_load_string($data);
        if($xml !== false) {
            switch (strtolower($xml->getName())) {
                case 'rss':
                    foreach ($xml->channel->item as $v) {
                        if($numShown++ >= $max) {
                            break;
                        }

                        printf("<div class=\"rss_title%s\"><a target='_blank' href='%s'>%s</a></div>", $context['TPortal']['rss_notitles'] ? '_normal' : '', trim($v->link), Util::htmlspecialchars(trim($v->title), ENT_QUOTES));

                        if(!$context['TPortal']['rss_notitles']) {
                            printf("<div class=\"rss_date\">%s</div><div class=\"rss_body\">%s</div>", $v->pubDate, $v->description);
                        }
                    }
                    break;
                case 'feed':
                    foreach ($xml->entry as $v) {
                        if($numShown++ >= $max) {
                            break;
                        }

                        printf("<div class=\"rss_title%s\"><a target='_blank' href='%s'>%s</a></div>", $context['TPortal']['rss_notitles'] ? '_normal' : '', trim($v->link['href']), Util::htmlspecialchars(trim($v->title), ENT_QUOTES));

                        if(!$context['TPortal']['rss_notitles']) {
                            printf("<div class=\"rss_date\">%s</div><div class=\"rss_body\">%s</div>", isset($v->issued) ? $v->issued : $v->published, $v->summary);
                        }
                    }
                    break;
            }
        }

    }}}

    public function collectArticleIcons() {{{
        global $context, $boardurl;

        $db = Database::getInstance();

        // get all themes for selection
        $context['TPthemes']  = array();
        $request =  $db->query('', '
            SELECT th.value AS name, th.id_theme as id_theme, tb.value AS path
            FROM {db_prefix}themes AS th
            LEFT JOIN {db_prefix}themes AS tb ON th.id_theme = tb.id_theme
            WHERE th.variable = {string:thvar}
            AND tb.variable = {string:tbvar}
            AND th.id_member = {int:mem_id}
            ORDER BY th.value ASC',
            array(
                'thvar' => 'name', 'tbvar' => 'images_url', 'mem_id' => 0,
            )
        );
        if(is_resource($request) && $db->num_rows($request) > 0)
        {
            while ($row = $db->fetch_assoc($request))
            {
                $context['TPthemes'][] = array(
                    'id' => $row['id_theme'],
                    'path' => $row['path'],
                    'name' => $row['name']
                );
            }
            $db->free_result($request);
        }


    }}}

    public function recordEvent($date, $id_member, $textvariable, $link, $description, $allowed, $eventid) {{{
        $db = Database::getInstance();

        $db->insert('insert',
            '{db_prefix}tp_events',
            array(
                'id_member'     => 'int',
                'date'          => 'int',
                'textvariable'  => 'string',
                'link'          => 'string',
                'description'   => 'string',
                'allowed'       => 'string',
                'eventid'       => 'int',
                'on'            => 'int',
            ),
            array($id_member, $date, $textvariable, $link, $description, $allowed, $eventid, 0),
            array('id')
        );
    }}}

    // Download an attachment.
    public function attach() {{{
        global $txt, $modSettings, $context;

        $db = Database::getInstance();

        // Some defaults that we need.
        $context['utf8'] = true;
        $context['no_last_modified'] = true;

        // Make sure some attachment was requested!
        if (!isset($_REQUEST['attach']) && !isset($_REQUEST['id'])) {
            fatal_lang_error('no_access', false);
        }

        $_REQUEST['attach'] = isset($_REQUEST['attach']) ? (int) $_REQUEST['attach'] : (int) $_REQUEST['id'];

        if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'avatar') {
            $request = $db->query('', '
                SELECT id_folder, filename, file_hash, fileext, id_attach, attachment_type, mime_type, approved
                FROM {db_prefix}attachments
                WHERE id_attach = {int:id_attach}
                    AND id_member > {int:blank_id_member}
                LIMIT 1',
                array(
                    'id_attach' => $_REQUEST['attach'],
                    'blank_id_member' => 0,
                )
            );
            $_REQUEST['image'] = true;
        }
        // This is just a regular attachment...
        else {
            $request = $db->query('', '
                SELECT a.id_folder, a.filename, a.file_hash, a.fileext, a.id_attach,
                    a.attachment_type, a.mime_type, a.approved
                FROM {db_prefix}attachments AS a
                WHERE a.id_attach = {int:attach}
                LIMIT 1',
                array(
                    'attach' => $_REQUEST['attach'],
                )
            );
        }
        if ($db->num_rows($request) == 0) {
            fatal_lang_error('no_access', false);
        }
        list ($id_folder, $real_filename, $file_hash, $file_ext, $id_attach, $attachment_type, $mime_type, $is_approved) = $db->fetch_row($request);
        $db->free_result($request);

        $filename = getAttachmentFilename($real_filename, $_REQUEST['attach'], $id_folder, false, $file_hash);

        // This is done to clear any output that was made before now. (would use ob_clean(), but that's PHP 4.2.0+...)
        ob_end_clean();
        if (!empty($modSettings['enableCompressedOutput']) && @version_compare(PHP_VERSION, '4.2.0') >= 0 && @filesize($filename) <= 4194304 && in_array($file_ext, array('txt', 'html', 'htm', 'js', 'doc', 'pdf', 'docx', 'rtf', 'css', 'php', 'log', 'xml', 'sql', 'c', 'java'))) {
            @ob_start('ob_gzhandler');
        }
        else {
            ob_start();
            header('Content-Encoding: none');
        }

        // No point in a nicer message, because this is supposed to be an attachment anyway...
        if (!file_exists($filename)) {
            self::loadLanguage('Errors');

            header('HTTP/1.0 404 ' . $txt['attachment_not_found']);
            header('Content-Type: text/plain; charset=UTF-8');

            // We need to die like this *before* we send any anti-caching headers as below.
            die('404 - ' . $txt['attachment_not_found']);
        }

        // If it hasn't been modified since the last time this attachement was retrieved, there's no need to display it again.
        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            list($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
            if (strtotime($modified_since) >= filemtime($filename)) {
                ob_end_clean();

                // Answer the question - no, it hasn't been modified ;).
                header('HTTP/1.1 304 Not Modified');
                exit;
            }
        }

        // Check whether the ETag was sent back, and cache based on that...
        $eTag = '"' . substr($_REQUEST['attach'] . $real_filename . filemtime($filename), 0, 64) . '"';
        if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $eTag) !== false) {
            ob_end_clean();

            header('HTTP/1.1 304 Not Modified');
            exit;
        }

        // Send the attachment headers.
        header('Pragma: ');

        if (!$context['browser']['is_gecko']) {
            header('Content-Transfer-Encoding: binary');
        }
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 525600 * 60) . ' GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
        header('Accept-Ranges: bytes');
        header('Set-Cookie:');
        header('Connection: close');
        header('ETag: ' . $eTag);

        // IE 6 just doesn't play nice. As dirty as this seems, it works.
        if ($context['browser']['is_ie6'] && isset($_REQUEST['image'])) {
            unset($_REQUEST['image']);
        }
        elseif (filesize($filename) != 0) {
            $size = @getimagesize($filename);
            if (!empty($size)) {
                // What headers are valid?
                $validTypes = array(
                    1 => 'gif',
                    2 => 'jpeg',
                    3 => 'png',
                    5 => 'psd',
                    6 => 'x-ms-bmp',
                    7 => 'tiff',
                    8 => 'tiff',
                    9 => 'jpeg',
                    14 => 'iff',
                );

                // Do we have a mime type we can simpy use?
                if (!empty($size['mime']) && !in_array($size[2], array(4, 13))) {
                    header('Content-Type: ' . strtr($size['mime'], array('image/bmp' => 'image/x-ms-bmp')));
                }
                elseif (isset($validTypes[$size[2]])) {
                    header('Content-Type: image/' . $validTypes[$size[2]]);
                }
                // Otherwise - let's think safety first... it might not be an image...
                elseif (isset($_REQUEST['image'])) {
                    unset($_REQUEST['image']);
                }
            }
            // Once again - safe!
            elseif (isset($_REQUEST['image'])) {
                unset($_REQUEST['image']);
            }
        }

        header('Content-Disposition: ' . (isset($_REQUEST['image']) ? 'inline' : 'attachment') . '; filename="' . $real_filename . '"');
        if (!isset($_REQUEST['image'])) {
            header('Content-Type: application/octet-stream');
        }

        // If this has an "image extension" - but isn't actually an image - then ensure it isn't cached cause of silly IE.
        if (!isset($_REQUEST['image']) && in_array($file_ext, array('gif', 'jpg', 'bmp', 'png', 'jpeg', 'tiff'))) {
            header('Cache-Control: no-cache');
        }
        else {
            header('Cache-Control: max-age=' . (525600 * 60) . ', private');
        }

        if (empty($modSettings['enableCompressedOutput']) || filesize($filename) > 4194304) {
            header('Content-Length: ' . filesize($filename));
        }
        // Try to buy some time...
        @set_time_limit(0);

        // Since we don't do output compression for files this large...
        if (filesize($filename) > 4194304) {
            // Forcibly end any output buffering going on.
            if (function_exists('ob_get_level')) {
                while (@ob_get_level() > 0)
                    @ob_end_clean();
            }
            else {
                @ob_end_clean();
                @ob_end_clean();
                @ob_end_clean();
            }

            $fp = fopen($filename, 'rb');
            while (!feof($fp)) {
                if (isset($callback)) {
                    echo $callback(fread($fp, 8192));
                }
                else {
                    echo fread($fp, 8192);
                }
                flush();
            }
            fclose($fp);
        }
        // On some of the less-bright hosts, readfile() is disabled.  It's just a faster, more byte safe, version of what's in the if.
        elseif (isset($callback) || @readfile($filename) == null) {
            echo isset($callback) ? $callback(file_get_contents($filename)) : file_get_contents($filename);
        }

        obExit(false);
    }}}

    public function art_recentitems($max = 5, $type = 'date' ) {{{

        $db = Database::getInstance();

        $now = forum_time();
        $data = array();
        $orderby = '';

        if($type == 'date')
            $orderby = 'art.date';
        elseif($type == 'views')
            $orderby = 'art.views';
        elseif($type == 'comments')
            $orderby = 'art.comments';

            $request = $db->query('', '
                SELECT art.id, art.date, art.subject, art.views, art.rating, art.comments
                FROM {db_prefix}tp_articles as art
                WHERE art.off = {int:off} and art.approved = {int:approved}
                AND ((art.pub_start = 0 AND art.pub_end = 0)
                    OR (art.pub_start != 0 AND art.pub_start < '. $now .' AND art.pub_end = 0)
                    OR (art.pub_start = 0 AND art.pub_end != 0 AND art.pub_end > '. $now .')
                    OR (art.pub_start != 0 AND art.pub_end != 0 AND art.pub_end > '. $now .' AND art.pub_start < '. $now .'))
                ORDER BY {raw:orderby} DESC LIMIT {int:limit}',
                array(
                    'off' => 0, 'approved' => 1, 'orderby' => $orderby, 'limit' => $max,
                )
            );

        if($db->num_rows($request) > 0) {
            while ($row = $db->fetch_assoc($request)) {
                $rat = explode(',', $row['rating']);
                if(is_countable($rat)) {
                    $rating_votes = count($rat);
                }
                else {
                    $rating_votes = 0;
                }
                if($row['rating'] == '') {
                    $rating_votes = 0;
                }
                $total = 0;
                foreach($rat as $mm => $mval) {
                    if(is_numeric($mval)) {
                        $total = $total + $mval;
                    }
                }
                if($rating_votes > 0 && $total > 0) {
                    $rating_average = floor($total / $rating_votes);
                }
                else {
                    $rating_average = 0;
                }

                $data[] = array(
                    'id' => $row['id'],
                    'subject' => $row['subject'],
                    'views' => $row['views'],
                    'date' => standardTime($row['date']),
                    'rating' => $rating_average,
                    'rating_votes' => $rating_votes,
                    'comments' => $row['comments'],
                );
            }
            $db->free_result($request);
        }
        return $data;
    }}}

    public function bbcbox($input) {{{
       echo'<div id="tp_smilebox"></div>';
       echo'<div id="tp_messbox"></div>';

       echo \template_control_richedit($input, 'tp_messbox', 'tp_smilebox');
    }}}

    public function prebbcbox($id, $body = '') {{{
        require_once(SUBSDIR . '/Editor.subs.php');

        $editorOptions = array(
            'id' => $id,
            'value' => $body,
            'preview_type' => 2,
            'height' => '300px',
            'width' => '100%',
        );
        \create_control_richedit($editorOptions);
    }}}

    public function getBlockStyles() {{{
        return array(
            '0' => array(
                'class' => 'titlebg+content',
                'code_title_left' => '<div class="title_bar"><h3 class="category_header">',
                'code_title_right' => '</h3></div>',
                'code_top' => '<div class="content tp_block21"><div>',
                'code_bottom' => '</div></div>',
            ),
            '1' => array(
                'class' => 'catbg+content',
                'code_title_left' => '<div class="cat_bar"><h3 class="category_header">',
                'code_title_right' => '</h3></div>',
                'code_top' => '<div><div class="content tp_block21">',
                'code_bottom' => '</div></div>',
            ),
            '2' => array(
                'class' => 'catbg+roundframe',
                'code_title_left' => '<div class="cat_bar"><h3 class="category_header">',
                'code_title_right' => '</h3></div>',
                'code_top' => '<div><div class="roundframe tp_block21">',
                'code_bottom' => '</div></div>',
            ),
            '3' => array(
                'class' => 'titletp+content',
                'code_title_left' => '<div class="tp_half21"><h3 class="category_header" style="font-size: 1.1em; height:auto;">',
                'code_title_right' => '</h3></div>',
                'code_top' => '<div class="content tp_block21"><div>',
                'code_bottom' => '</div></div>',
            ),
            '4' => array(
                'class' => 'cattp+content',
                'code_title_left' => '<div class="tp_half21"><h3 class="category_header">',
                'code_title_right' => '</h3></div>',
                'code_top' => '<div class="content tp_block21"><div>',
                'code_bottom' => '</div></div>',
            ),
            '5' => array(
                'class' => 'titlebg+content',
                'code_title_left' => '<div class="title_bar"><h3 class="category_header">',
                'code_title_right' => '</h3></div>',
                'code_top' => '<div class="content tp_block21"><div>',
                'code_bottom' => '</div></div>',
            ),
            '6' => array(
                'class' => 'catbg+content',
                'code_title_left' => '<div class="cat_bar"><h3 class="category_header">',
                'code_title_right' => '</h3></div>',
                'code_top' => '<div><div class="content tp_block21">',
                'code_bottom' => '</div></div>',
            ),

            '7' => array(
                'class' => 'catbg+roundframe2',
                'code_title_left' => '<div class="cat_bar"><h3 class="category_header">',
                'code_title_right' => '</h3></div>',
                'code_top' => '<div class="roundframe tp_block21"><div>',
                'code_bottom' => '</div></div>',
            ),
            '8' => array(
                'class' => 'titletp+content',
                'code_title_left' => '<div class="tp_half21"><h3 class="category_header" style="font-size: 1.1em; height:auto;">',
                'code_title_right' => '</h3></div>',
                'code_top' => '<div><div class="content tp_block21">',
                'code_bottom' => '</div></div>',
            ),
            '9' => array(
                'class' => 'cattp+roundframe2',
                'code_title_left' => '<div class="tp_half21"><h3 class="category_header">',
                'code_title_right' => '</h3></div>',
                'code_top' => '<div class="roundframe tp_block21"><div>',
                'code_bottom' => '</div></div>',
            ),
        );
    }}}

    public function grps($save = true, $noposts = true) {{{
        global $context, $txt;

        $db = Database::getInstance();

        // get all membergroups for permissions
        $context['TPmembergroups'] = array();
        if($noposts)
        {
            $context['TPmembergroups'][] = array(
                'id' => '-1',
                'name' => $txt['tp-guests'],
                'posts' => '-1'
            );
            $context['TPmembergroups'][] = array(
                'id' => '0',
                'name' => $txt['tp-ungroupedmembers'],
                'posts' => '-1'
            );
        }
        $request = $db->query('', '
            SELECT id_group as id_group, group_name as group_name, min_posts as min_posts
            FROM {db_prefix}membergroups
            WHERE '. ($noposts ? 'min_posts = -1 AND id_group > 1' : '1') .'
            ORDER BY id_group'
        );

        while ($row = $db->fetch_assoc($request))
        {
            $context['TPmembergroups'][] = array(
                'id' => $row['id_group'],
                'name' => $row['group_name'],
                'posts' => $row['min_posts']
            );
        }
        $db->free_result($request);

        if($save)
            return $context['TPmembergroups'];
    }}}

    public function convertphp($code, $reverse = false) {{{

        if(!$reverse) {
            return $code;
        }
        else {
            return $code;
        }
    }}}

    public function updateSettings($addSettings) {{{
        global $context;

        $tpAdmin = Admin::getInstance();

        if (empty($addSettings) || !is_array($addSettings)) {
            return;
        }

        foreach ($addSettings as $variable => $value) {
            $id = $tpAdmin->getSettingData('id', array ( 'name' => $variable ));
            if(is_array($id)) {
                $tpAdmin->updateSetting($id[0]['id'], array( 'value' => ($value === true ? 'value + 1' : ($value === false ? 'value - 1' : $value))));
            }
            else {
                $tpAdmin->insertSetting(array( 'name' => $variable, 'value' => ($value === true ? 'value + 1' : ($value === false ? 'value - 1' : $value))));
            }
            $context['TPortal'][$variable] = $value === true ? $context['TPortal'][$variable] + 1 : ($value === false ? $context['TPortal'][$variable] - 1 : $value);
        }
        // Clean out the cache and make sure the cobwebs are gone too.
        \ElkArte\Cache\Cache::instance()->put('tpSettings', null, 90);

        return;
    }}}

    public function getMemberColour($member_ids) {{{
        if (empty($member_ids)) {
            return false;
        }

        $db = Database::getInstance();

        $member_ids = is_array($member_ids) ? $member_ids : array($member_ids);

        $request = $db->query('', '
                SELECT mem.id_member, mgrp.online_color AS mg_online_color, pgrp.online_color AS pg_online_color
                FROM {db_prefix}members AS mem
                LEFT JOIN {db_prefix}membergroups AS mgrp
                    ON (mgrp.id_group = mem.id_group)
                LEFT JOIN {db_prefix}membergroups AS pgrp
                    ON (pgrp.id_group = mem.id_post_group)
                WHERE mem.id_member IN ({array_int:member_ids})',
                array(
                    'member_ids'	=> $member_ids,
                )
        );

        $mcol = array();
        if($db->num_rows($request) > 0) {
            while ($row = $db->fetch_assoc($request)) {
                $mcol[$row['id_member']]    = !empty($row['mg_online_color']) ? $row['mg_online_color'] : $row['pg_online_color'];
            }
            $db->free_result($request);
        }

        return $mcol;
    }}}

    // profile summary
    public function profile_summary($member_id) {{{
        global $txt, $context;
        $context['page_title'] = $txt['tpsummary'];
        // get all articles written by member
        $max_art = Article::getInstance()->getTotalAuthorArticles($member_id);
        $context['TPortal']['tpsummary']=array(
            'articles' => $max_art,
        );
    }}}

    // articles and comments made by the member
    public function profile_articles($member_id) {{{
        global $txt, $context, $scripturl;

        $db = Database::getInstance();

        $context['page_title'] = $txt['articlesprofile'];
        $context['TPortal']['member_id'] = $member_id;

        $tpArticle  = Article::getInstance();
        $start      = 0;
        $sorting    = 'date';

        if(isset($context['TPortal']['mystart'])) {
            $start = is_numeric($context['TPortal']['mystart']) ? $context['TPortal']['mystart'] : 0;
        }

        if($context['TPortal']['tpsort'] != '') {
            $sorting = $context['TPortal']['tpsort'];
            if(!in_array($sorting, array('date', 'subject', 'views', 'category', 'comments'))) {
                $sorting = 'date';
            }
        }

        // get all articles written by member
        $max        = $tpArticle->getTotalAuthorArticles($member_id, false, true);

        // get all not approved articles
        $max_approve= $tpArticle->getTotalAuthorArticles($member_id, false, false);

        // get all articles currently being off
        $max_off    = $tpArticle->getTotalAuthorArticles($member_id, true, true);

        $context['TPortal']['all_articles']         = $max;
        $context['TPortal']['approved_articles']    = $max_approve;
        $context['TPortal']['off_articles']         = $max_off;

        $request = $db->query('', '
            SELECT art.id, art.date, art.subject, art.approved, art.off, art.comments, art.views, art.rating, art.voters,
                art.author_id as authorID, art.category, art.locked
            FROM {db_prefix}tp_articles AS art
            WHERE art.author_id = {int:auth}
            ORDER BY art.{raw:sort} {raw:sorter} LIMIT 15 OFFSET {int:start}',
            array('auth' => $member_id,
            'sort' => $sorting,
            'sorter' => in_array($sorting, array('date', 'views', 'comments')) ? 'DESC' : 'ASC',
            'start' => $start
            )
        );

        if($db->num_rows($request) > 0){
            while($row = $db->fetch_assoc($request)) {
                $rat = array();
                $rating_votes = 0;
                $rat = explode(',', $row['rating']);
                $rating_votes = count($rat);
                if($row['rating'] == '') {
                    $rating_votes = 0;
                }
                $total = 0;
                foreach($rat as $mm => $mval) {
                    if(is_numeric($mval)) {
                        $total = $total + $mval;
                    }
                }
                if($rating_votes > 0 && $total > 0) {
                    $rating_average = floor($total / $rating_votes);
                }
                else {
                    $rating_average = 0;
                }
                $can_see = true;
                if(($row['approved'] != 1 || $row['off'] == 1)) {
                    $can_see = allowedTo('tp_articles');
                }
                if($can_see) {
                    $context['TPortal']['profile_articles'][] = array(
                        'id' => $row['id'],
                        'subject' => $row['subject'],
                        'date' => standardTime($row['date']),
                        'timestamp' => $row['date'],
                        'href' => '' . $scripturl . '?page='.$row['id'],
                        'comments' => $row['comments'],
                        'views' => $row['views'],
                        'rating_votes' => $rating_votes,
                        'rating_average' => $rating_average,
                        'approved' => $row['approved'],
                        'off' => $row['off'],
                        'locked' => $row['locked'],
                        'catID' => $row['category'],
                        'category' => '<a href="'.$scripturl.'?mycat='.$row['category'].'">' . (isset($context['TPortal']['catnames'][$row['category']]) ? $context['TPortal']['catnames'][$row['category']] : '') .'</a>',
                        'editlink' => allowedTo('tp_articles') ? $scripturl.'?action=admin;area=tparticles;sa=editarticle'.$row['id'] : $scripturl.'?action=tportal;sa=editarticle'.$row['id'],
                    );
                }
            }
            $db->free_result($request);
        }

        // construct pageindexes
        $context['TPortal']['pageindex'] = '';
        if($max > 0) {
            $context['TPortal']['pageindex'] = self::pageIndex($scripturl.'?action=profile;area=tpadmin;sa=tparticles;u='.$member_id.';tpsort='.$sorting, $start, $max, '15');
        }

        // setup subaction
		$context['TPortal']['profile_action'] = '';
        /*
        if(isset($_GET['sa']) && $_GET['sa'] == 'settings') {
            $context['TPortal']['profile_action'] = 'settings';
        }
		*/


        // Create the tabs for the template.
        $context[$context['profile_menu_name']]['tab_data'] = array(
            'title' => $txt['articlesprofile'],
            'description' => $txt['articlesprofile2'],
            'tabs' => array(
                'articles' => array(),
            ),
        );


        if(self::loadLanguage('TPortalAdmin') == false) {
            self::loadLanguage('TPortalAdmin', 'english');
        }

    }}}


    public function tp_articles($member_id = null) {{{
        global $txt, $context;

        if(is_null($member_id)) {
            $member_id = Util::filter('u', 'get', 'int');
        }

        ArticleCategories();
        loadtemplate('TPprofile');
        $context['page_title'] = $txt['articlesprofile'];
        tp_profile_articles($member_id);
    }}}

    public function createthumb($picture, $width, $height, $thumb) {{{

        //code modified from http://www.akemapa.com/2008/07/10/php-gd-resize-transparent-image-png-gif/
        //Check if GD extension is loaded
        if (!extension_loaded('gd') && !extension_loaded('gd2')) {
            trigger_error("GD is not loaded", E_USER_WARNING);
            return false;
        }

        //Get Image size info
        $pictureInfo = getimagesize($picture);
        switch ($pictureInfo[2]) {
            case 1: $im = imagecreatefromgif($picture); break;
            case 2: $im = imagecreatefromjpeg($picture);  break;
            case 3: $im = imagecreatefrompng($picture); break;
            default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
        }

        //If image dimension is smaller, do not resize
        if ($pictureInfo[0] <= $width && $pictureInfo[1] <= $height) {
            $nHeight = $pictureInfo[1];
            $nWidth = $pictureInfo[0];
        }
        else {
            //yeah, resize it, but keep it proportional
            if ($width/$pictureInfo[0] > $height/$pictureInfo[1]) {
                $nWidth = $width;
                $nHeight = $pictureInfo[1]*($width/$pictureInfo[0]);
            }
            else {
                $nWidth = $pictureInfo[0]*($height/$pictureInfo[1]);
                $nHeight = $height;
            }
        }

        $nWidth     = round($nWidth);
        $nHeight    = round($nHeight);

        $newpicture = imagecreatetruecolor($nWidth, $nHeight);

        /* Check if this image is PNG or GIF, then set if Transparent*/
        if(($pictureInfo[2] == 1) OR ($pictureInfo[2]==3)) {
            imagealphablending($newpicture, false);
            imagesavealpha($newpicture,true);
            $transparent = imagecolorallocatealpha($newpicture, 255, 255, 255, 127);
            imagefilledrectangle($newpicture, 0, 0, $nWidth, $nHeight, $transparent);
        }
        imagecopyresampled($newpicture, $im, 0, 0, 0, 0, $nWidth, $nHeight, $pictureInfo[0], $pictureInfo[1]);

        //Generate the file, and rename it to $thumb
        switch ($pictureInfo[2]) {
            case 1: imagegif($newpicture,$thumb); break;
            case 2: imagejpeg($newpicture,$thumb);  break;
            case 3: imagepng($newpicture,$thumb); break;
            default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
        }

        return $thumb;
    }}}

    public function uploadpicture($widthhat, $prefix, $maxsize='1800', $exts='jpg,gif,png', $destdir = 'tp-images') {{{
        global $txt;

        self::loadLanguage('TPortal');

        $upload = Upload::getInstance();

        if(!is_null($maxsize)) {
            $upload->set_max_file_size($maxsize);
        }

        if(is_null($exts)) {
            $exts = array('jpg', 'gif', 'png');
        }
        elseif(is_string($exts)) {
            $exts = explode(',', $exts);
        }
        $upload->set_mime_types($exts);

        // add prefix
        $name   = $_FILES[$widthhat]['name'];
        $name   = $upload->check_filename($name);
        $sname  = $prefix.$name;

        if(is_dir($destdir)) {
            $dstPath = $destdir . '/' . $sname;
        }
        else {
            $dstPath = BOARDDIR . '/'. $destdir .'/' . $sname;
        }

        if($upload->check_file_exists($dstPath)) {
            $dstPath = dirname($dstPath) . '/' . $prefix . $upload->generate_filename(dirname($dstPath)) . $sname;
        }

        if($upload->upload_file($_FILES[$widthhat]['tmp_name'], $dstPath) === FALSE) {
            unlink($_FILES[$widthhat]['tmp_name']);
            $error_string = sprintf($txt['tp-notuploaded'], $upload->get_error(TRUE));
            throw new \Elk_Exception($error_string, 'general');
        }

        return basename($dstPath);
    }}}

    public function langfiles() {{{
        global $context, $settings, $boarddir;

        // get all languages for blocktitles
        $context['TPortal']['langfiles'] = array();
		$dirs = array();
        
		$language_dirs = array ( $settings['default_theme_dir'] . '/languages' , $boarddir.'/TinyPortal/Views/languages');
		foreach($language_dirs as $language_dir) {
			$dir = dir($language_dir);
			while ($entry = $dir->read()) {
				if($entry != '.' && $entry != '..' && is_dir($language_dir.'/'.$entry)) {
					$dirs[] = $language_dir.'/'.$entry;
				}
			}
			$dir->close();
		}

		foreach($dirs as $language_dir) {
			$dir = dir($language_dir);
			while ($entry = $dir->read()) {
				if ((substr($entry, 0, 8) == 'TPortal.') && (substr($entry,(strlen($entry) - 4) , 4) == '.php') && (strlen($entry) > 12)) {
					$context['TPortal']['langfiles'][] = substr(substr($entry, 8), 0, -4);
				}
			}
			$dir->close();
		}

    }}}

    public function catLayouts() {{{
        global $context, $txt;

        // setup the layoutboxes
        $context['TPortal']['admin_layoutboxes'] = array(
            array('value' => '1', 'label' => $txt['tp-catlayout1']),
            array('value' => '2', 'label' => $txt['tp-catlayout2']),
            array('value' => '4', 'label' => $txt['tp-catlayout4']),
            array('value' => '8', 'label' => $txt['tp-catlayout8']),
            array('value' => '6', 'label' => $txt['tp-catlayout6']),
            array('value' => '5', 'label' => $txt['tp-catlayout5']),
            array('value' => '3', 'label' => $txt['tp-catlayout3']),
            array('value' => '9', 'label' => $txt['tp-catlayout9']),
            array('value' => '7', 'label' => $txt['tp-catlayout7']),
        );
    }}}

    public function boards() {{{
        global $context;

        $db = Database::getInstance();

        $context['TPortal']['boards'] = array();
        $request = $db->query('', '
            SELECT b.id_board as id, b.name, b.board_order
            FROM {db_prefix}boards as b
            WHERE 1=1
            ORDER BY b.board_order ASC',
            array()
        );
        if($db->num_rows($request) > 0) {
            while($row = $db->fetch_assoc($request)) {
                $context['TPortal']['boards'][] = $row;
            }
            $db->free_result($request);
        }
    }}}

    public function articles() {{{

        global $context;

        $db = Database::getInstance();

        $context['TPortal']['edit_articles'] = array();

        $request = $db->query('', '
            SELECT id, subject, shortname FROM {db_prefix}tp_articles
            WHERE approved = 1 AND off = 0
            ORDER BY subject ASC');

        if($db->num_rows($request) > 0) {
            while($row=$db->fetch_assoc($request)) {
                $context['TPortal']['edit_articles'][] = $row;
            }

            $db->free_result($request);
        }
    }}}

    public function create_dir($path) {{{

        require_once(SUBSDIR . '/Package.subs.php');

        // Load up the package FTP information?
        \create_chmod_control();

        if (!\mktree($path, 0755)) {
            \deltree($path, true);
            throw new \Elk_Exception($txt['tp-failedcreatedir'], 'general');
        }

        return TRUE;
    }}}

    public function delete_dir($path) {{{

        require_once(SUBSDIR . '/Package.subs.php');

        // Load up the package FTP information?
        \create_chmod_control();

        \deltree($path, true);

        return TRUE;
    }}}

    public function recursive_copy($src, $dst) {{{

        $dir = opendir($src);
        self::create_dir($dst);
        while(false !== ($file = readdir($dir)) ) {
            if(($file != '.') && ($file != '..')) {
                if(is_dir($src . '/' . $file)) {
                    self::recursive_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);

    }}}

    public function groups() {{{
        global $txt;

        $db = Database::getInstance();
        // get all membergroups for permissions
        $grp    = array();
        $grp[]  = array(
            'id' =>'-1',
            'name' => $txt['tp-guests'],
            'posts' => '-1'
        );
        $grp[]  = array(
            'id' => '0',
            'name' => $txt['tp-ungroupedmembers'],
            'posts' => '-1'
        );

        $request =  $db->query('', '
            SELECT * FROM {db_prefix}membergroups
            WHERE 1=1 ORDER BY id_group'
        );
        while ($row = $db->fetch_assoc($request)) {
            $grp[] = array(
                'id' => $row['id_group'],
                'name' => $row['group_name'],
                'posts' => $row['min_posts']
            );
        }
        return $grp;
    }}}

    public function loadLanguage($template_name, $lang = '', $fatal = true, $force_reload = false) {{{
        global $user_info, $language, $txt;

        if ($lang == '') {
		    $lang = isset($user_info['language']) ? $user_info['language'] : $language;
        }

		// Always load english
		$filePath = BOARDDIR . '/TinyPortal/Views/languages/english/'.$template_name.'.english.php';
        if(file_exists($filePath)) {
            require_once($filePath);
		}

        foreach( array ( $lang ) as $l) {
            $filePath = BOARDDIR . '/TinyPortal/Views/languages/'.$l.'/'.$template_name.'.'.$l.'.php';
            if(file_exists($filePath)) {
                require_once($filePath);
                return $lang;
            }
        }

        return \ElkArte\Themes\ThemeLoader::loadLanguageFile($template_name, $lang, $fatal, $force_reload);

    }}}

    public function getAvatars($ids) {{{
        global $user_info, $modSettings, $scripturl;
        global $image_proxy_enabled, $image_proxy_secret, $boardurl;

        $db = \database();

        $request = $db->query('', '
            SELECT
                mem.real_name, mem.member_name, mem.id_member, mem.show_online,mem.avatar, mem.email_address AS email_address,
                COALESCE(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type AS attachment_type
            FROM {db_prefix}members AS mem
            LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member AND a.attachment_type != 3)
            WHERE mem.id_member IN ({array_int:ids})',
            array('ids' => $ids)
        );

        $avy = array();
        if($db->num_rows($request) > 0) {
            while ($row = $db->fetch_assoc($request)) {
                $avy[$row['id_member']] = \determineAvatar( array(
                        'avatar'            => $row['avatar'],
                        'email_address'     => $row['email_address'],
                        'filename'          => !empty($row['filename']) ? $row['filename'] : '',
                        'id_attach'         => $row['id_attach'],
                        'attachment_type'   => $row['attachment_type'],
                    )
                )['image'];
            }
            $db->free_result($request);
        }

        return $avy;
    }}}

	public function parse_bbc($bbc) {{{

		return \BBC\ParserWrapper::instance()->parseMessage($bbc, TRUE);

	}}}

	public function standardTime($time) {{{

		return \standardTime($time);

	}}}
}

?>
