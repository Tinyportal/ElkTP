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

global $db_prefix, $package_log, $type;

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
            array('name' => 'name', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '') ),
            array('name' => 'value', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '') ),
        ),
        'indexes' => array(
            array('type' => 'primary', 'columns' => array('id'),),
        ),
    ),
    'tp_blocks' => array(
        'columns' => array(
            array('name' => 'id', 'type' => 'int', 'size' => 11, 'auto' => true,),
            array('name' => 'type', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
			array('name' => 'frame', 'type' => 'tinytext', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'title', 'type' => 'tinytext', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'body', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'bar', 'type' => 'smallint', 'size' => 4, 'default' => 0 ),
			array('name' => 'pos', 'type' => 'int', 'size' => 11, 'default' => 0 ),
            array('name' => 'off', 'type' => 'smallint', 'size' => 1, 'default' => 0 ),
			array('name' => 'visible', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'lang', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'access', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'display', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'editgroups', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'settings', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
        ),
        'indexes' => array(
            array('type' => 'primary', 'columns' => array('id')),
        ),
    ),
    'tp_variables' => array(
        'columns' => array(
            array('name' => 'id', 'type' => 'int', 'size' => 11, 'auto' => true,),
            array('name' => 'value1', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'value2', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'value3', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'type', 'type' => 'tinytext', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'value4', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'value5', 'type' => 'int', 'size' => 11, 'default' => -2,),
			array('name' => 'subtype', 'type' => 'tinytext', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'value7', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'value8', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
			array('name' => 'subtype2', 'type' => 'int', 'size' => 11, 'default' => 0,),
			array('name' => 'value9', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
        ),
        'indexes' => array(
            array('type' => 'primary', 'columns' => array('id')),
        ),
    ),
    'tp_articles' => array(
        'columns' => array(
            array('name' => 'id', 'type' => 'int', 'size' => 11, 'auto' => true,),
            array('name' => 'date', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'body', 'type' => ($type == 'mysql' ? 'longtext' : 'text'), 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'intro', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'useintro', 'type' => 'smallint', 'size' => 1, 'default' => 0,),
            array('name' => 'category', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'frontpage', 'type' => 'smallint', 'size' => 1, 'default' => 0,),
            array('name' => 'subject', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '') ),
            array('name' => 'author_id', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'author', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '') ),
            array('name' => 'frame', 'type' => 'tinytext', 'default' => ($type == 'mysql' ? null : '') ),
            array('name' => 'approved', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'off', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'options', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'parse', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'comments', 'type' => 'smallint', 'size' => 4, 'default' => 0,),
            array('name' => 'comments_var', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'views', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'rating', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'voters', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'id_theme', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'shortname', 'type' => 'tinytext', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'sticky', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'fileimport', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'topic', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'locked', 'type' => 'smallint', 'size' => 6, 'default' => 0,),
            array('name' => 'illustration', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'headers', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'type', 'type' => 'tinytext', 'default' => ($type == 'mysql' ? null : '')),
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
            array('name' => 'item_type', 'type' => 'varchar', 'size' => 255, 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'item_id', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'datetime', 'type' => 'int', 'size' => 11, 'default' => 0,),
            array('name' => 'subject', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'comment', 'type' => 'text', 'default' => ($type == 'mysql' ? null : '')),
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
            array('name' => 'textvariable', 'type' => 'mediumtext', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'link', 'type' => 'mediumtext', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'description', 'type' => 'mediumtext', 'default' => ($type == 'mysql' ? null : '')),
            array('name' => 'allowed', 'type' => 'mediumtext', 'default' => ($type == 'mysql' ? null : '')),
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

$settings_array = array(
    // KEEP TRACK OF INTERNAL VERSION HERE
    'version' => '2.1.0',
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
    'boardnews_headerstyle' => 'catbg',
    'boardnews_divbody' => 'windowbg noup',
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
    // Shoutbox
    'show_shoutbox_smile' => '1',
    'show_shoutbox_icons' => '1',
    'shout_allow_links' => '0',
    'shoutbox_usescroll' => '0',
    'shoutbox_scrollduration' => '5',
    'shoutbox_refresh' => '0',
    'shout_submit_returnkey' => '0',
    'shoutbox_limit' => '5',
    'shoutbox_maxlength' => '256',
    'shoutbox_timeformat' => 'Y M d H:i:s',
    'shoutbox_use_groupcolor' => '1',
    'shoutbox_textcolor' => '#000',
    'shoutbox_timecolor' => '#787878',
    'shoutbox_linecolor1' => '#f0f4f7',
    'shoutbox_linecolor2' => '#fdfdfd',
    'profile_shouts_hide' => '0',
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
    // Downloads
    'show_download' => '1',
    'dl_allowed_types' => 'zip,rar,doc,docx,pdf,jpg,gif,png',
    'dl_max_upload_size' => '2000',
    'dl_fileprefix' => 'K',
    'dl_usescreenshot' => '1',
    'dl_screenshotsizes' => '80,80,200,200',
    'dl_approve' => '1',
    'dl_createtopic' => 1,
    'dl_createtopic_boards' => '',
    'dl_wysiwyg' => 'html',
    'dl_introtext' => '<p><strong>Welcome to the TinyPortal download manager!</strong></p>
<p><br></p>
<p>TPdownloads is a built-in function for TinyPortal that lets you offer files for your members to browse and download. It works by having the downloadable files placed in categories. These categories have permissions on them, letting you restrict membergroups access level for each category. You may also allow members to upload files, control which membergroups are allowed and what types of files they may upload.<br><br>Admins can access the TPdownloads settings from the menu &quot;Tinyportal &gt; Manage TPdownloads&quot; and select the [Settings] button.<br></p>
<p>If you do not wish to use TPdownloads you can deactivate the function completely by setting the option "TPdownloads is NOT active" in the settings. The TPdownloads menu option will no longer be displayed in the menu when TPdownloads is deactivated.</p>
<p><br></p>
<p>We hope you enjoy using TinyPortal.&nbsp; If you have any problems, please feel free to <a href="https://www.tinyportal.net/index.php">ask us for assistance</a>.<br></p>
<p><br>Thanks!<br>The TinyPortal team</p>',	
    'dl_showfeatured' => '1',
    'dl_featured' => '',	
    'dl_showlatest' => '1',
    'dl_showstats' => '1',
    'dl_showcategorytext' => '1',
    'dl_visual_options' => 'left,right,center,top',	
    'dlmanager_theme' => '0',
    'dl_allow_upload' => '1',
    'dl_approve_groups' => '',
);

$updates    = 0;
$bars       = array('leftpanel' => 'leftbar', 'rightpanel' => 'rightbar', 'toppanel' => 'topbar', 'centerpanel' => 'centerbar', 'bottompanel' => 'bottombar', 'lowerpanel' => 'lowerbar');
$barskey    = array_keys($bars);

$updateSettings = array( 'userbox_options', 'download_upload_path', 'blockcode_upload_path' );

$db         = database();

foreach($settings_array as $what => $val) {
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
	elseif($db->num_rows($request) > 0 && $what == 'version'){
		$db->query('', '
            UPDATE {db_prefix}tp_settings
            SET value = {string:val}
            WHERE name = {string:name}',
            array('val' => $val, 'name' => $what)
        );
		$render .= '<li>Updated internal version number to '.$val.'</li>';
		$db->free_result($request);
	}
	elseif($db->num_rows($request) > 0 && in_array($what, $updateSettings)){
		$db->query('', '
            UPDATE {db_prefix}tp_settings
            SET value = {string:val}
            WHERE name = {string:name}',
            array('val' => $val, 'name' => $what)
        );
		$db->free_result($request);
	}
    elseif($db->num_rows($request) > 0 && in_array($what, $barskey)) {
        $row = $db->fetch_row($request);
        $val = $row[2];
        $db->query('', '
            UPDATE {db_prefix}tp_settings
            SET value = {string:val}
            WHERE name = {string:name}',
            array('val' => $val, 'name' => $what)
        );
        $db->query('', '
            UPDATE {db_prefix}tp_settings
            SET value = {string:val}
            WHERE name = {string:name}',
            array('val' => '0', 'name' => $bars[$what])
        );
    }
	else {
		$db->free_result($request);
    }
}

?>
