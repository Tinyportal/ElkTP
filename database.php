<?php
/**
 * install.php
 *
 * @package TinyPortal
 * @version 2.1.0
 * @author IchBin - http://www.tinyportal.net
 * @founder Bloc
 * @license MPL 2.0
 *
 * The contents of this file are subject to the Mozilla Public License Version 2.0
 * (the "License"); you may not use this package except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Copyright (C) 2020 - The TinyPortal Team
 *
 */

define('TP_MINIMUM_PHP_VERSION', '7.2.0');

global $db_prefix, $package_log, $db_type;

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('ELK')) {
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('ELK')) {
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as ElkArte\'s index.php.');
}

if ((!function_exists('version_compare') || version_compare(TP_MINIMUM_PHP_VERSION, PHP_VERSION, '>='))) {
	die('<strong>Install Error:</strong> - please install a version of php greater than '.TP_MINIMUM_PHP_VERSION);
}

$tables = array(
    'tp_data' => array(
        'columns' => array(
            array('name' => 'id', 'type' => 'int', 'size' => 11, 'auto' => true,),
            array('name' => 'type', 'type' => 'smallint', 'size' => 4, 'default' => 0,),
            array('name' => 'id_member', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'value', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'item', 'type' => 'int', 'size' => 11, 'default' => 0,),
        ),
        'indexes' => array(
            array('type' => 'primary', 'columns' => array('id'),),
        ),
    ),
    'tp_settings' => array(
        'columns' => array(
            array('name' => 'id', 'type' => 'mediumint', 'size' => 9, 'auto' => true,),
            array('name' => 'name', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '') ),
            array('name' => 'value', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '') ),
        ),
        'indexes' => array(
            array('type' => 'primary', 'columns' => array('id'),),
        ),
    ),
    'tp_blocks' => array(
        'columns' => array(
            array('name' => 'id', 'type' => 'int', 'size' => 11, 'auto' => true,),
            array('name' => 'type', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
			array('name' => 'frame', 'type' => 'tinytext', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'title', 'type' => 'tinytext', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'body', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'bar', 'type' => 'smallint', 'size' => 4, 'default' => 0 ),
			array('name' => 'pos', 'type' => 'int', 'size' => 11, 'default' => 0 ),
            array('name' => 'off', 'type' => 'smallint', 'size' => 1, 'default' => 0 ),
			array('name' => 'visible', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'lang', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'access', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'display', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'editgroups', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'settings', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
        ),
        'indexes' => array(
            array('type' => 'primary', 'columns' => array('id')),
        ),
    ),
    'tp_variables' => array(
        'columns' => array(
            array('name' => 'id', 'type' => 'int', 'size' => 11, 'auto' => true,),
            array('name' => 'value1', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'value2', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'value3', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'type', 'type' => 'tinytext', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'value4', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'value5', 'type' => 'int', 'size' => 11, 'default' => -2,),
			array('name' => 'subtype', 'type' => 'tinytext', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'value7', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'value8', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
			array('name' => 'subtype2', 'type' => 'int', 'size' => 11, 'default' => 0,),
			array('name' => 'value9', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
        ),
        'indexes' => array(
            array('type' => 'primary', 'columns' => array('id')),
        ),
    ),
    'tp_articles' => array(
        'columns' => array(
            array('name' => 'id', 'type' => 'int', 'size' => 11, 'auto' => true,),
            array('name' => 'date', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'body', 'type' => ($db_type == 'mysql' ? 'longtext' : 'text'), 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'intro', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'useintro', 'type' => 'smallint', 'size' => 1, 'default' => 0,),
            array('name' => 'category', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'frontpage', 'type' => 'smallint', 'size' => 1, 'default' => 0,),
            array('name' => 'subject', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '') ),
            array('name' => 'author_id', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'author', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '') ),
            array('name' => 'frame', 'type' => 'tinytext', 'default' => ($db_type == 'mysql' ? null : '') ),
            array('name' => 'approved', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'off', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'options', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'parse', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'comments', 'type' => 'smallint', 'size' => 4, 'default' => 0,),
            array('name' => 'comments_var', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'views', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'rating', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'voters', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'id_theme', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'shortname', 'type' => 'tinytext', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'sticky', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'fileimport', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'topic', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'locked', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'illustration', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'headers', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'type', 'type' => 'tinytext', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'featured', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'pub_start', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'pub_end', 'type' => 'int', 'size' => 11, 'default' => 0,),
        ),
        'indexes' => array(
            array('type' => 'primary', 'columns' => array('id')),
        ),
    ),
    'tp_comments' => array (
        'columns' => array (
            array('name' => 'id', 'type' => 'int', 'size' => 11, 'auto' => true,),
            array('name' => 'item_type', 'type' => 'varchar', 'size' => 255, 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'item_id', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'datetime', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'subject', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'comment', 'type' => 'text', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'member_id', 'type' => 'int', 'size' => 11, 'default' => 0,),
        ),
        'indexes' => array (
            array('type' => 'primary', 'columns' => array('id')),
        ),
    ),
    'tp_events' => array(
        'columns' => array(
            array('name' => 'id', 'type' => 'int', 'size' => 11, 'auto' => true,),
            array('name' => 'id_member', 'type' => 'int', 'size' => 11, 'default' => 0),
			array('name' => 'date', 'type' => 'int', 'size' => 11, 'default' => 0),
            array('name' => 'textvariable', 'type' => 'mediumtext', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'link', 'type' => 'mediumtext', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'description', 'type' => 'mediumtext', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'allowed', 'type' => 'mediumtext', 'default' => ($db_type == 'mysql' ? null : '')),
            array('name' => 'eventid', 'type' => 'int', 'size' => 11, 'default' => 0),
            array('name' => 'on', 'type' => 'smallint', 'size' => 4, 'default' => 0),
        ),
        'indexes' => array(
            array('type' => 'primary', 'columns' => array('id')),
        ),
    ),
);

$db_table   = db_table();

// Create the tables, if they don't already exist
foreach ($tables as $tp_table => $data) {
    $db_table->db_create_table('{db_prefix}' . $tp_table, $data['columns'], $data['indexes'], array(), 'ignore');
}

$settingsArray = array(
    // KEEP TRACK OF INTERNAL VERSION HERE
    'version' => '1.0.0',
    'frontpage_title' => '',
    'showforumfirst' => '0',
    'hideadminmenu' => '0',
    'useroundframepanels' => '0',
    'showcollapse' => '1',
    'blocks_edithide' => '0',
    'uselangoption' => '0',
    'use_groupcolor' => '0',
    'maxstars' => '5',
    'showstars' => '1',
    'oldsidebar' => '1',
    'admin_showblocks' => '1',
    'imageproxycheck' => '1',
    'fulltextsearch' => '0',
    'disable_template_eval' => '1',
    'copyrightremoval' => '',
    'image_upload_path'     => BOARDDIR.'/tp-images/',
    'download_upload_path'  => BOARDDIR.'/tp-downloads/',
    'blockcode_upload_path' => BOARDDIR.'/tp-files/tp-blockcodes/',
    // frontpage
    'front_type' => 'forum_articles',
    'frontblock_type' => 'first',
    'frontpage_visual' => 'left,right,center,top,bottom,lower,header',
    'frontpage_layout' => '1',
    'frontpage_catlayout' => '1',
    'frontpage_template' => '',	
    'allow_guestnews' => '1',
    'SSI_board' => '1',
    'frontpage_limit' => '5',
    'frontpage_limit_len' => '300',
    'frontpage_topics' => '',
    'forumposts_avatar' => '1',
    'use_attachment' => '0',
    'boardnews_divheader' => 'cat_bar',
    'boardnews_headerstyle' => 'category_header',
    'boardnews_divbody' => 'content',
    // article settings
    'use_wysiwyg' => '2',
    'editorheight' => '400',
    'use_dragdrop' => '0',
    'hide_editarticle_link' => '1',
    'print_articles' => '1',
    'allow_links_article_comments' => '1',
    'hide_article_facebook' => '0',
    'hide_article_twitter' => '0',
    'hide_article_reddit' => '0',
    'hide_article_digg' => '0',
    'hide_article_delicious' => '0',
    'hide_article_stumbleupon' => '0',
    'icon_width' => '100',
    'icon_height' => '100',
    'icon_max_size' => '500',
    'art_imagesizes' => '80,40,400,200',
    // Panels
    'hidebars_admin_only' => '1',
    'hidebars_profile' => '1',
    'hidebars_pm' => '1',
    'hidebars_memberlist' => '1',
    'hidebars_search' => '1',
    'hidebars_calendar' => '1',
    'hidebars_custom' => '',
    'padding' => '4',
    'leftbar_width' => '200',
    'rightbar_width' => '230',
    'showtop' => '1',
    'leftpanel' => '1',
    'rightpanel' => '1',
    'toppanel' => '1',
    'centerpanel' => '1',
    'frontpanel' => '1',
    'lowerpanel' => '1',
    'bottompanel' => '1',
    'hide_leftbar_forum' => '0',
    'hide_rightbar_forum' => '0',
    'hide_topbar_forum' => '0',
    'hide_centerbar_forum' => '0',
    'hide_lowerbar_forum' => '0',
    'hide_bottombar_forum' => '0',
    'block_layout_left' => 'vert',
    'block_layout_right' => 'vert',
    'block_layout_top' => 'vert',
    'block_layout_center' => 'vert',
    'block_layout_front' => 'vert',
    'block_layout_lower' => 'vert',
    'block_layout_bottom' => 'vert',
    'blockgrid_left' => '',
    'blockgrid_right' => '',
    'blockgrid_top' => '',
    'blockgrid_center' => '',
    'blockgrid_front' => '',
    'blockgrid_lower' => '',
    'blockgrid_bottom' => '',
    'blockwidth_left' => '200',
    'blockwidth_right' => '150',
    'blockwidth_top' => '150',
    'blockwidth_center' => '150',
    'blockwidth_front' => '150',
    'blockwidth_lower' => '150',
    'blockwidth_bottom' => '150',
    'blockheight_left' => '',
    'blockheight_right' => '',
    'blockheight_top' => '',
    'blockheight_center' => '',
    'blockheight_front' => '',
    'blockheight_lower' => '',
    'blockheight_bottom' => '',
    'panelstyle_left' => '0',
    'panelstyle_right' => '0',
    'panelstyle_top' => '0',
    'panelstyle_upper' => '0',
    'panelstyle_center' => '0',
    'panelstyle_front' => '0',
    'panelstyle_lower' => '0',
    'panelstyle_bottom' => '0',
    // Other	
    'bottombar' => '1',
    'cat_list' => '1,2',
    'featured_article' => '0',
    'redirectforum' => '1',
    'resp' => '0',
    'rss_notitles' => '0',
    'sitemap_items' => '3',
    'temapaths' => '',
    'userbox_options' => 'avatar,logged,time,unread,stats,online,stats_all',
);

$updateSettings = array( 'userbox_options', 'download_upload_path', 'blockcode_upload_path', 'version' );

$db             = database();
foreach($settingsArray as $what => $val) {
	$request = $db->query('', '
        SELECT * FROM {db_prefix}tp_settings
        WHERE name = {string:name}',
        array('name' => $what)
    );

	if($db->num_rows($request) < 1) {
		$db->insert('ignore',
            '{db_prefix}tp_settings',
            array('name' => 'string', 'value' => 'string'),
            array($what, $val),
            array('id')
        );
		$updates++;
	}
	elseif($db->num_rows($request) > 0 && in_array($what, $updateSettings)){
		$db->query('', '
            UPDATE {db_prefix}tp_settings
            SET value = {string:val}
            WHERE name = {string:name}',
            array('val' => $val, 'name' => $what)
        );
    }

	$db->free_result($request);
}

addDefaults();

function addDefaults() {{{
	global $boardurl;

    $db = database();

	// Check for blocks in table, if none insert default blocks.
	$request = $db->query('', '
		SELECT * FROM {db_prefix}tp_blocks LIMIT 1'
	);

	if ($db->num_rows($request) < 1) {
        $blocks = array(
            'search' =>array(
                'type' => 4,
                'frame' => 'theme',
                'title' => 'Search',
                'body' => '',
                'access' => '-1,0,1,2,3',
                'bar' => 1,
                'pos' => 0,
                'off' => 0,
                'visible' => '',
                'lang' => '',
                'display' => 'allpages',
                'editgroups' => '',
                'settings' => json_encode( array ('var1' => 0, 'var2' => '0', 'var3' => 0, 'var4' => 0, 'var5' => 0) ),
            ),
            'user' =>array(
                'type' => 1,
                'frame' => 'theme',
                'title' => 'User',
                'body' => '',
                'access' => '-1,0,1,2,3',
                'bar' => 1,
                'pos' => 1,
                'off' => 0,
                'visible' => '',
                'lang' => '',
                'display' => 'allpages',
                'editgroups' => '',
                'settings' => json_encode( array ('var1' => 0, 'var2' => '0', 'var3' => 0, 'var4' => 0, 'var5' => 0) ),
            ),
            'recent' =>array(
                'type' => 12,
                'frame' => 'theme',
                'title' => 'Recent',
                'body' => '10',
                'access' => '-1,0,1,2,3',
                'bar' => 2,
                'pos' => 0,
                'off' => 0,
                'visible' => '',
                'lang' => '',
                'display' => 'allpages',
                'editgroups' => '',
                'settings' => json_encode( array ('var1' => 1, 'var2' => '0', 'var3' => 0, 'var4' => 0, 'var5' => 0) ),
            ),
            'stats' =>array(
                'type' => 3,
                'frame' => 'theme',
                'title' => 'Stats',
                'body' => '10',
                'access' => '-1,0,1,2,3',
                'bar' => 2,
                'pos' => 1,
                'off' => 0,
                'visible' => '',
                'lang' => '',
                'display' => 'allpages',
                'editgroups' => '',
                'settings' => json_encode( array ('var1' => 0, 'var2' => '0', 'var3' => 0, 'var4' => 0, 'var5' => 0) ),
            ),
            'online' =>array(
                'type' => 6,
                'frame' => 'theme',
                'title' => 'Online',
                'body' => '',
                'access' => '-1,0,1,2,3',
                'bar' => 3,
                'pos' => 0,
                'off' => 0,
                'visible' => '0',
                'lang' => '',
                'display' => 'allpages',
                'editgroups' => '-2',
                'settings' => json_encode( array ('var1' => 1, 'var2' => '0', 'var3' => 0, 'var4' => 0, 'var5' => 0) ),
            ),
        );
        
        $db->insert('ignore',
            '{db_prefix}tp_blocks',
            array(
                'type' => 'int',
                'frame' => 'string',
                'title' => 'string',
                'body' => 'string',
                'access' => 'string',
                'bar' => 'int',
                'pos' => 'int',
                'off' => 'int',
                'visible' => 'string',
                'lang' => 'string',
                'display' => 'string',
                'editgroups' => 'string',
                'settings' => 'string',
            ),
            $blocks,
            array('id')
        );
        $db->free_result($request);
	}

	// Check for date in variables table, if none insert default values.
	$request = $db->query('', '
		SELECT * FROM {db_prefix}tp_variables LIMIT 1'
	);

	if ($db->num_rows($request) < 1) {
		$vars = array(
			'var1' =>array(
				'value1' => 'Portal features',
				'value2' => '0',
				'value3' => '-1,0,2,3',
				'type' => 'category',
				'value4' => '',
				'value5' => -2,
				'subtype' => '',
				'value7' => 'sort=date|sortorder=desc|articlecount=5|layout=1|catlayout=1|showchild=0|leftpanel=1|rightpanel=1|toppanel=1|centerpanel=1|lowerpanel=1|bottompanel=1',
				'value8' => 'Features',
				'subtype2' => 0,
				'value9' => '',
			),
			'var2' =>array(
				'value1' => 'General Articles',
				'value2' => '0',
				'value3' => '-1,0,2,3',
				'type' => 'category',
				'value4' => '',
				'value5' => -2,
				'subtype' => 'General',
				'value7' => 'sort=date|sortorder=desc|articlecount=5|layout=1|catlayout=1|showchild=0|leftpanel=1|rightpanel=1|toppanel=1|centerpanel=1|lowerpanel=1|bottompanel=1',
				'value8' => '',
				'subtype2' => 0,
				'value9' => '',
			),
			'var3' =>array(
				'value1' => 'Demo Articles',
				'value2' => '0',
				'value3' => 'cats1',
				'type' => 'menubox',
				'value4' => '0',
				'value5' => 10,
				'subtype' => '',
				'value7' => '',
				'value8' => '',
				'subtype2' => 0,
				'value9' => '',
			),
		);

		$db->insert('ignore',
			'{db_prefix}tp_variables',
			array(
				'value1' => 'string',
				'value2' => 'string',
				'value3' => 'string',
				'type' => 'string',
				'value4' => 'string',
				'value5' => 'int',
				'subtype' => 'string',
				'value7' => 'string',
				'value8' => 'string',
				'subtype2' => 'int',
				'value9' => 'string',
			),
			$vars,
			array('id')
		);
		$db->free_result($request);
	}

}}}

?>
