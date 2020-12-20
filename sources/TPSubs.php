<?php
/**
 * @package TinyPortal
 * @version 1.0.0
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
use \TinyPortal\Article as TPArticle;
use \TinyPortal\Permissions as TPPermissions;
use \TinyPortal\Util as TPUtil;


if (!defined('ELK')) {
	die('Hacking attempt...');
}

function TPcollectSnippets() {{{
	global $context;

	// fetch any blockcodes in blockcodes folder
	$codefiles = array();
	if ($handle = opendir($context['TPortal']['blockcode_upload_path'])) {
		while (false !== ($file = readdir($handle))) {
			if($file != '.' && $file != '..' && $file != '.htaccess' && substr($file, (strlen($file) - 10), 10) == '.blockcode') {
				$snippet = TPparseModfile(file_get_contents($context['TPortal']['blockcode_upload_path'] . $file), array('name', 'author', 'version', 'date', 'description'));
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

function TPparseModfile($file , $returnarray) {{{
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

function TPArticleCategories($use_sorted = false) {{{
	global $context, $txt;

    $db = database();

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
		SELECT value2, COUNT(*) as siblings
		FROM {db_prefix}tp_variables
		WHERE type = {string:type} GROUP BY value2',
		array(
			'type' => 'category'
		)
	);
	if($db->num_rows($request2) > 0)
	{
		while($row = $db->fetch_assoc($request2))
		{
			$total2[$row['value2']] = $row['siblings'];
		}
		$db->free_result($request2);
	}

	$request =  $db->query('', '
		SELECT cats.*
		FROM {db_prefix}tp_variables as cats
		WHERE cats.type = {string:type}
		ORDER BY cats.value1 ASC',
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
			$opts = explode('|' , $row['value7']);
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
			if($row['value2'] == $row['id'] || $row['value2'] == '' || $row['value2'] == '0')
				$row['value2'] = 9999;
			// check access
			$show = get_perm($row['value3']);
			if($show) {
				$sorted[$row['id']] = array(
					'id' => $row['id'],
					'shortname' => !empty($row['value8']) ? $row['value8'] : $row['id'],
					'name' => $row['value1'],
					'parent' => $row['value2'],
					'access' => $row['value3'],
					'icon' => $row['value4'],
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
				$context['TPortal']['catnames'][$row['id']]=$row['value1'];
				$context['TPortal']['categories_shortname'][$sorted[$row['id']]['shortname']]=$row['id'];
			}
		}
		$db->free_result($request);
	}
	$context['TPortal']['article_categories'] = array();
	if($use_sorted) {
		// sort them
		if(count($sorted)>1) {
			$context['TPortal']['article_categories'] = chain('id', 'parent', 'name', $sorted);
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

function chain($primary_field, $parent_field, $sort_field, $rows, $root_id = 0, $maxlevel = 25) {{{
   $c = new chain($primary_field, $parent_field, $sort_field, $rows, $root_id, $maxlevel);
   return $c->chain_table;
}}}

class chain
{
   var $table;
   var $rows;
   var $chain_table;
   var $primary_field;
   var $parent_field;
   var $sort_field;

   function __construct($primary_field, $parent_field, $sort_field, $rows, $root_id, $maxlevel)
   {
       $this->rows = $rows;
       $this->primary_field = $primary_field;
       $this->parent_field = $parent_field;
       $this->sort_field = $sort_field;
       $this->buildChain($root_id,$maxlevel);
   }

   function buildChain($rootcatid,$maxlevel)
   {
       foreach($this->rows as $row)
       {
           $this->table[$row[$this->parent_field]][ $row[$this->primary_field]] = $row;
       }
       $this->makeBranch($rootcatid, 0, $maxlevel);
   }

   function makeBranch($parent_id, $level, $maxlevel)
   {
       if(!is_array($this->table))
              $this->table = array();

       if(!array_key_exists($parent_id, $this->table))
              return;

       $rows = $this->table[$parent_id];
       foreach($rows as $key=>$value)
       {
           $rows[$key]['key'] = $this->sort_field;
       }

       usort($rows, 'chainCMP');
       foreach($rows as $item)
       {
           $item['indent'] = $level;
           $this->chain_table[] = $item;
           if((isset($this->table[$item[$this->primary_field]])) && (($maxlevel > $level + 1) || ($maxlevel == 0)))
           {
               $this->makeBranch($item[$this->primary_field], $level + 1, $maxlevel);
           }
       }
   }
}

function chainCMP($a, $b) {{{
   if($a[$a['key']] == $b[$b['key']]) {
       return 0;
   }
   return($a[$a['key']] < $b[$b['key']]) ? -1 : 1;
}}}

function TP_permaTheme($theme)
{
	global $context;

    $db = database();

	$me = $context['user']['id'];
	$db->query('', '
		UPDATE {db_prefix}members
		SET id_theme = {int:theme}
		WHERE id_member = {int:mem_id}',
		array(
			'theme' => $theme, 'mem_id' => $me,
		)
	);

	if(isset($context['TPortal']['querystring']))
		$tp_where = str_replace(array(';permanent'), array(''), $context['TPortal']['querystring']);
	else
		$tp_where = 'action=forum;';

	redirectexit($tp_where);
}

function TP_error($text)
{
	global $context;

	$context['TPortal']['tperror'] = $text;
	$context['template_layers'][] = 'tperror';
}

function tp_renderbbc($message)
{
	global $context, $txt;

	$descriptionEditorOptions = array(
		'id' => 'description',
		'value' => $context['theme']['description'],
		// We do XML preview here.
		'preview_type' => 0,
		// Specify the size
		'rows' => 7,
		'columns' => 120,
		'width' => '99%',
	);
	create_control_richedit($descriptionEditorOptions);

	// We do not yet support spell checking.
	$context['show_spellchecking'] = false;
	$context['can_post_team'] = siteAllowedTo('postAsTeam');
	$context['sub_template'] = 'themepost';
	$context['page_title'] = $context['editing'] ? $txt['ts_editing_theme'] . $context['theme']['name'] : $txt['ts_add_new_theme'];
	loadTemplate('Post');

	echo '
			<tr>
				<td class="content" colspan="2">';

		echo '
				</td>
			</tr>';
}

function get_snippets_xml() {{{
	return;
}}}

function TP_createtopic($title, $text, $icon, $board, $sticky = 0, $submitter) {{{
	global $user_info, $board_info, $sourcedir;

	require_once($sourcedir.'/Subs-Post.php');

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

function TPwysiwyg_setup() {{{
	global $context, $boardurl, $txt;

	$context['html_headers'] .= '
		<link rel="stylesheet" href="'.$boardurl.'/themes/default/scripts/tinyportal/sceditor/minified/themes/default.min.css" />
		<script src="'.$boardurl.'/themes/default/scripts/tinyportal/sceditor/minified/sceditor.min.js"></script>
		<script src="'.$boardurl.'/themes/default/scripts/tinyportal/sceditor/minified/formats/xhtml.js"></script>
		<script src="'.$boardurl.'/themes/default/scripts/tinyportal/sceditor/languages/'.$txt['lang_dictionary'].'.js"></script>
		<style>
			.sceditor-button-floatleft div { background: url('.$boardurl.'/themes/default/images/tinyportal/floatleft.png); width:24px; height:24px; margin: -3px; }
			.sceditor-button-floatright div { background: url('.$boardurl.'/themes/default/images/tinyportal/floatright.png); width:24px; height:24px; margin: -3px; }
		</style>';

	$context['html_headers'] .= '
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
			// Taken from ELK2.1 https://github.com/SimpleMachines/ELK2.1/blob/24a10ca4fcac45f0bd73b6185618217aaa531cd2/themes/default/scripts/jquery.sceditor.smf.js#L289
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
		$context['html_headers'] .= '
			<script src="'.$boardurl.'/themes/default/scripts/tinyportal/sceditor/minified/plugins/dragdrop.js"></script>
			<script type="text/javascript"><!-- // --><![CDATA[
			function detectIE() {
				var ua = window.navigator.userAgent;

				// Test values; Uncomment to check result

				// IE 10
				// ua = \'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)\';

				// IE 11
				// ua = \'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko\';

				// Edge 12 (Spartan)
				// ua = \'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36 Edge/12.0\';

				// Edge 13
				// ua = \'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586\';

				var msie = ua.indexOf(\'MSIE \');
				if (msie > 0) {
					// IE 10 or older => return version number
					return parseInt(ua.substring(msie + 5, ua.indexOf(\'.\', msie)), 10);
				}

				var trident = ua.indexOf(\'Trident/\');
				if (trident > 0) {
					// IE 11 => return version number
					var rv = ua.indexOf(\'rv:\');
					return parseInt(ua.substring(rv + 3, ua.indexOf(\'.\', rv)), 10);
				}

				var edge = ua.indexOf(\'Edge/\');
				if (edge > 0) {
					// Edge (IE 12+) => return version number
					return parseInt(ua.substring(edge + 5, ua.indexOf(\'.\', edge)), 10);
				}

				// other browser
				return false;
			}
			// Get IE or Edge browser version
			var version = detectIE();

			if (version === false) {
				// Do nothing
			} else {
				document.write(\'<script src="https://cdnjs.cloudflare.com/ajax/libs/bluebird/3.3.5/bluebird.min.js"><\/script>\');
				document.write(\'<script src="https://cdnjs.cloudflare.com/ajax/libs/fetch/2.0.3/fetch.min.js"><\/script>\');
			}
			// ]]></script>';
	}
}}}

function TPwysiwyg($textarea, $body, $upload = true, $uploadname, $use = 1, $showchoice = true) {{{
    global $context, $scripturl, $txt, $boardurl, $user_info;

	echo '
	<div style="padding-top: 10px;">
		<textarea style="width: 100%; height: ' . $context['TPortal']['editorheight'] . 'px;" name="'.$textarea.'" id="'.$textarea.'">'.$body.'</textarea>';

	if($context['TPortal']['use_dragdrop']) {
		echo '<script type="text/javascript"><!-- // --><![CDATA[
			function tpImageUpload(file) {
				var form = new FormData();
				form.append(\'image\', file);
				return fetch(\''.$scripturl.'?action=tportal;sa=uploadimage\', {
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
				style: \''.$boardurl.'/themes/default/scripts/tinyportal/sceditor/minified/themes/content/default.min.css\',
				emoticonsRoot: \''.$boardurl.'/themes/default/scripts/tinyportal/sceditor/\'
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
				echo '<img src="', str_replace($boarddir, $boardurl, $context['TPortal']['image_upload_path']), substr($im,6) , '"  alt="'.substr($im,6).'" title="'.substr($im,6).'" />';
            }
		}

		echo '
		</div>
		</div>
		<div class="tp_pad">' , $txt['tp-uploadfile'] ,'<input type="file" name="'.$uploadname.'"></div>
	</div>';
	}

}}}

function tp_fetchpermissions($perms) {{{
	global $txt;

    $db = database();

	$perm = array();
	if(is_array($perms))
	{
		$request = $db->query('', '
			SELECT p.permission, m.group_name AS group_name, p.id_group AS id_group
			FROM {db_prefix}permissions AS p
            INNER JOIN {db_prefix}membergroups AS m
			    ON p.id_group = m.id_group
			WHERE p.add_deny = {int:deny}
			AND p.permission IN ({array_string:tag})
			AND m.min_posts = {int:minpost}
			ORDER BY m.group_name ASC',
			array('deny' => 1, 'tag' => $perms, 'minpost' => -1)
		);
		if($db->num_rows($request) > 0)
		{
			while ($row = $db->fetch_assoc($request))
			{
				$perm[$row['permission']][$row['id_group']] = $row['id_group'];
			}
			$db->free_result($request);
		}
		// special for members
		$request =  $db->query('', '
			SELECT p.permission, p.id_group
			FROM {db_prefix}permissions as p
			WHERE p.add_deny = {int:deny}
			AND p.id_group IN (0, -1)
			AND p.permission IN ({array_string:tag})',
			array('deny' => 1, 'tag' => $perms)
		);
		if($db->num_rows($request) > 0)
		{
			while ($row = $db->fetch_assoc($request))
			{
				$perm[$row['permission']][$row['id_group']] = $row['id_group'];
			}
			$db->free_result($request);
		}
		return $perm;
	}
	else
	{
		$names = array();
		$request = $db->query('', '
			SELECT m.group_name as group_name, m.id_group as id_group
			FROM {db_prefix}membergroups as m
			WHERE m.min_posts = {int:minpost}
			ORDER BY m.group_name ASC',
			array('minpost' => -1)
		);
		if($db->num_rows($request) > 0)
		{
			// set regaular members
			$names[0] = array(
				'id' => 0,
				'name' => $txt['members'],
			);
			while ($row = $db->fetch_assoc($request))
			{
				$names[$row['id_group']] = array(
					'id' => $row['id_group'],
					'name' => $row['group_name'],
				);
			}
			$db->free_result($request);
		}
		return $names;
	}
}}}

function tp_fetchboards()
{
    $db = database();

	// get all boards for board-spesific news
	$request =  $db->query('', '
		SELECT id_board, name, board_order
		FROM {db_prefix}boards
		WHERE  1=1
		ORDER BY board_order ASC',
		array()
	);
	$boards = array();
	if ($db->num_rows($request) > 0)
	{
		while($row = $db->fetch_assoc($request))
			$boards[] = array('id' => $row['id_board'], 'name' => $row['name']);

		$db->free_result($request);
	}
	return $boards;
}

function tp_hidepanel($id, $inline = false, $string = false, $margin='')
{
	global $context, $settings;

	$what = '
	<a style="' . (!$inline ? 'float: right;' : '') . ' cursor: pointer;" onclick="togglepanel(\''.$id.'\')">
		<img id="toggle_' . $id . '" src="' . $settings['tp_images_url'] . '/TPupshrink' . (in_array($id, $context['tp_panels']) ? '2' : '') . '.png" ' . (!empty($margin) ? 'style="margin: '.$margin.';"' : '') . 'alt="*" />
	</a>';
	if($string)
		return $what;
	else
		echo $what;
}

function tp_hidepanel2($id, $id2, $alt)
{
	global $txt, $context, $settings;

	$what = '
	<a title="'.$txt[$alt].'" style="cursor: pointer;" onclick="togglepanel(\''.$id.'\');togglepanel(\''.$id2.'\')">
		<img id="toggle_' . $id . '" src="' . $settings['tp_images_url'] . '/TPupshrink' . (in_array($id, $context['tp_panels']) ? '2' : '') . '.png" alt="*" />
	</a>';

	return $what;
}

function TP_fetchprofile_areas() {{{

	$areas = array(
		'tp_summary' => array('name' => 'tp_summary', 'permission' => 'profile_view_any'),
		'tp_articles' => array('name' => 'tp_articles', 'permission' => 'tp_articles'),
	);

    call_integration_hook('integrate_tp_profile_areas', array(&$areas));
    
	return $areas;
}}}

function TP_fetchprofile_areas2($member_id) {{{
	global $context, $scripturl, $txt, $user_info;

	if (!$user_info['is_guest'] && (($context['user']['is_owner'] && allowedTo('profile_view_own')) || allowedTo(array('profile_view_any', 'moderate_forum', 'manage_permissions','tp_dlmanager','tp_blocks','tp_articles','tp_gallery','tp_linkmanager')))) {
		$context['profile_areas']['tinyportal'] = array(
			'title' => $txt['tp-profilesection'],
			'areas' => array()
		);

		$context['profile_areas']['tinyportal']['areas']['tp_summary'] = '<a href="' . $scripturl . '?action=profile;u=' . $member_id . ';sa=tp_summary">' . $txt['tpsummary'] . '</a>';
		if ($context['user']['is_owner'] || allowedTo('tp_articles')) {
			$context['profile_areas']['tinyportal']['areas']['tp_articles'] = '<a href="' . $scripturl . '?action=profile;u=' . $member_id . ';sa=tp_articles">' . $txt['articlesprofile'] . '</a>';
        }
    
        call_integration_hook('integrate_tp_profile', array(&$member_id));
	}

}}}

function get_perm($perm, $moderate = '') {{{   
    return TPPermissions::getInstance()->getPermissions($perm, $moderate);	
}}}

function tpsort($a, $b)
{
	return strnatcasecmp($b["timestamp"], $a["timestamp"]);
}

// add to the linktree
function TPadd_linktree($url,$name)
{
	global $context;

	$context['linktree'][] = array('url' => $url, 'name' => $name);
}

// strip the linktree
function TPstrip_linktree()
{
	global $context, $scripturl;

	$context['linktree'] = array();
	$context['linktree'][] = array('url' => $scripturl, 'name' => $context['forum_name']);
}

// Constructs a page list.
function TPageIndex($base_url, &$start, $max_value, $num_per_page)
{
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
}

function tp_renderarticle($intro = '')
{
	global $context, $txt, $scripturl, $boarddir;
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
			eval(tp_convertphp($context['TPortal']['article']['intro'], true));
            $data .= ob_get_clean();
		}
		elseif($context['TPortal']['article']['rendertype'] == 'bbc' || $context['TPortal']['article']['rendertype'] == 'import') {
            if(TPUtil::isHTML($context['TPortal']['article']['intro']) || isset($context['TPortal']['article']['parsed_bbc'])) {
			    $data .= $context['TPortal']['article']['intro'];
            } 
            else {
                $data .= parse_bbc($context['TPortal']['article']['intro']);
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
			eval(tp_convertphp($context['TPortal']['article']['body'], true));
            $data .= ob_get_clean();
		}
		elseif($context['TPortal']['article']['rendertype'] == 'bbc') {
            if(TPUtil::isHTML($context['TPortal']['article']['body']) || isset($context['TPortal']['article']['parsed_bbc'])) {
			    $data .= $context['TPortal']['article']['body'];
            } 
            else {
			    $data .= parse_bbc($context['TPortal']['article']['body']);
            }

            if(!empty($context['TPortal']['article']['readmore'])) {
                $data .= $context['TPortal']['article']['readmore'];
            }
		}
		elseif($context['TPortal']['article']['rendertype'] == 'import') {
			if(!file_exists($boarddir. '/' . $context['TPortal']['article']['fileimport'])) {
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
}

function tp_renderblockarticle()
{

	global $context, $txt, $boarddir;

	// just return if data is missing
	if(!isset($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]))
		return;

	echo '
	<div class="article_inner">';
	if($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['rendertype'] == 'php')
		eval($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['body']);
	elseif($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['rendertype'] == 'import')
	{
		if(!file_exists($boarddir. '/' . $context['TPortal']['blockarticles'][$context['TPortals']['blockarticle']]['fileimport']))
			echo '<em>' , $txt['tp-cannotfetchfile'] , '</em>';
		else
			include($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['fileimport']);
	}
	elseif($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['rendertype']=='bbc')
		echo parse_bbc($context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['body']);
	else
		echo $context['TPortal']['blockarticles'][$context['TPortal']['blockarticle']]['body'];
	echo '
	</div>';
	return;
}

function render_template($code, $render = true)
{
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
}

function render_template_layout($code, $prefix = '')
{
    global $context;

    if(!empty($context['TPortal']['disable_template_eval'])) { 
        if(preg_match_all('~(?<={)([A-Za-z0-9]+)(?=})~', $code, $match) !== false) {
            foreach($match[0] as $suffix) {
                $func = (string)"$prefix$suffix";
                if(function_exists($func)) {
                    ob_start();
                    $func();
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
}

function tp_hidebars($what = 'all' )
{
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
}

function TPgetlangOption($langlist, $set)
{

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
}

function category_col($column, $featured = false, $render = true)
{
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
            render_template($context['TPortal']['article']['template'], $render);
        }
        else {
            if(function_exists('ctheme_article_renders')) {
                render_template(ctheme_article_renders($context['TPortal']['category']['options']['catlayout'], false, $featured), $render);
            }
            else {
                render_template(article_renders($context['TPortal']['category']['options']['catlayout'], false, $featured), $render);
            }
        }
        unset($context['TPortal']['article']);
    }
}

// the featured or first article
function category_featured( $render = true)
{
    return category_col('featured', true, $render);

}
// the first half
function category_col1($render = true)
{
    return category_col('col1', false, $render);
}

// the second half
function category_col2($render = true)
{
    return category_col('col2', false, $render);
}

function TPparseRSS($override = '', $encoding = 0)
{
	global $context;

	// Initialise the number of RSS Feeds to show
	$numShown = 0;

	$backend = isset($context['TPortal']['rss']) ? $context['TPortal']['rss'] : '';
	if($override != '')
		$backend = $override;

	$allow_url = ini_get('allow_url_fopen');
	if ($allow_url){
  		$xml = simplexml_load_file($backend);
	} else {
  		$curl = curl_init();
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  		curl_setopt($curl, CURLOPT_URL, $backend);
  		$ret = curl_exec($curl);
  		curl_close($curl);
		$xml = simplexml_load_string($ret);
	}
	
	if($xml !== false) {
		switch (strtolower($xml->getName())) {
			case 'rss':
				foreach ($xml->channel->item as $v) {
					if($numShown++ >= $context['TPortal']['rssmaxshown'])
						break;

					printf("<div class=\"rss_title%s\"><a target='_blank' href='%s'>%s</a></div>", $context['TPortal']['rss_notitles'] ? '_normal' : '', trim($v->link), TPUtil::htmlspecialchars(trim($v->title), ENT_QUOTES));

					if(!$context['TPortal']['rss_notitles'])
						printf("<div class=\"rss_date\">%s</div><div class=\"rss_body\">%s</div>", $v->pubDate, $v->description);
				}
				break;
			case 'feed':
				foreach ($xml->entry as $v) {
					if($numShown++ >= $context['TPortal']['rssmaxshown'])
						break;

					printf("<div class=\"rss_title%s\"><a target='_blank' href='%s'>%s</a></div>", $context['TPortal']['rss_notitles'] ? '_normal' : '', trim($v->link['href']), TPUtil::htmlspecialchars(trim($v->title), ENT_QUOTES));

					if(!$context['TPortal']['rss_notitles'])
						printf("<div class=\"rss_date\">%s</div><div class=\"rss_body\">%s</div>", isset($v->issued) ? $v->issued : $v->published, $v->summary);
				}
				break;
		}
	}

}

// Set up the administration sections.
function TPadminIndex($tpsub = '', $module_admin = false) {{{
	global $txt, $context, $scripturl;

	if(loadLanguage('TPortalAdmin') == false)
		loadLanguage('TPortalAdmin', 'english');

	if($module_admin) {
		// make sure tpadmin is still active
		$_GET['action'] = 'tpadmin';
	}

	$context['admin_tabs'] = array();
	$context['admin_header']['tp_settings'] = $txt['tp-adminheader1'];
	$context['admin_header']['tp_articles'] = $txt['tp-articles'];
	$context['admin_header']['tp_blocks']   = $txt['tp-adminpanels'];

	if (allowedTo('tp_settings')) {
		$context['admin_tabs']['tp_settings'] = array(
			'settings' => array(
				'title' => $txt['tp-settings'],
				'description' => $txt['tp-settingdesc1'],
				'href' => $scripturl . '?action=admin;area=tpsettings;sa=settings',
				'is_selected' => $tpsub == 'settings',
			),
			'frontpage' => array(
				'title' => $txt['tp-frontpage'],
				'description' => $txt['tp-frontpagedesc1'],
				'href' => $scripturl . '?action=admin;area=tpsettings;sa=frontpage',
				'is_selected' => $tpsub == 'frontpage',
			),
		);
	}
	if (allowedTo('tp_editownarticle')) {
		$context['admin_tabs']['tp_articles'] = array(
			'myarticles' => array(
				'title' => $txt['tp-myarticles'],
				'description' => $txt['tp-articledesc1'],
				'href' => $scripturl . '?action=tportal;sa=myarticles',
				'is_selected' => $tpsub == 'myarticles',
			),
		);
	}
	
	if (allowedTo('tp_articles')) {
		$context['admin_tabs']['tp_articles'] = array(
			'articles' => array(
				'title' => $txt['tp-articles'],
				'description' => $txt['tp-articledesc1'],
				'href' => $scripturl . '?action=admin;area=tparticles;sa=articles',
				'is_selected' => (substr($tpsub,0,11)=='editarticle' || in_array($tpsub, array('articles','addarticle','addarticle_php', 'addarticle_bbc', 'addarticle_import','strays','submission'))),
			),
			'categories' => array(
				'title' => $txt['tp-tabs5'],
				'description' => $txt['tp-articledesc2'],
				'href' => $scripturl . '?action=admin;area=tparticles;sa=categories',
				'is_selected' => in_array($tpsub, array('categories', 'addcategory','clist')) ,
			),
			'artsettings' => array(
				'title' => $txt['tp-settings'],
				'description' => $txt['tp-articledesc3'],
				'href' => $scripturl . '?action=admin;area=tparticles;sa=artsettings',
				'is_selected' => $tpsub == 'artsettings',
			),
			'icons' => array(
				'title' => $txt['tp-adminicons'],
				'description' => $txt['tp-articledesc5'],
				'href' => $scripturl . '?action=admin;area=tparticles;sa=articons',
				'is_selected' => $tpsub == 'articons',
			),
		);
	}

	if (allowedTo('tp_blocks')) {
		$context['admin_tabs']['tp_blocks'] = array(
			'panelsettings' => array(
				'title' => $txt['tp-allpanels'],
				'description' => $txt['tp-paneldesc1'],
				'href' => $scripturl . '?action=admin;area=tpblocks;sa=panels',
				'is_selected' => $tpsub == 'panels',
			),
			'blocks' => array(
				'title' => $txt['tp-allblocks'],
				'description' => $txt['tp-blocksdesc1'],
				'href' => $scripturl . '?action=admin;area=tpblocks;sa=blocks',
				'is_selected' => $tpsub == 'blocks' && !isset($_GET['latest']) && !isset($_GET['overview']),
			),
			'blockoverview' => array(
				'title' => $txt['tp-blockoverview'],
				'description' => '',
				'href' => $scripturl . '?action=admin;area=tpblocks;sa=blocks;overview',
				'is_selected' => ($tpsub == 'blocks' && isset($_GET['overview'])) || substr($tpsub,0,9) == 'editblock',
			),
		);
	}

    call_integration_hook('integrate_tp_admin_areas');

	validateSession();

}}}

function tp_collectArticleIcons()
{
	global $context, $boarddir, $boardurl;

    $db = database();

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

	$count = 1;
	$context['TPortal']['articons'] = array();
	$context['TPortal']['articons']['illustrations'] = array();

	$sorted2 = array();
	//illustrations/images
	if ($handle = opendir($boarddir.'/tp-files/tp-articles/illustrations'))
	{
		while (false !== ($file = readdir($handle)))
		{
			if($file != '.' && $file != '..' && $file != '.htaccess' && $file != 'TPno_illustration.png' && in_array(strtolower(substr($file, strlen($file) -4, 4)), array('.gif', '.jpg', '.png')))
			{
				if(substr($file, 0, 2) == 's_')
					$context['TPortal']['articons']['illustrations'][] = array(
						'id' => $count,
						'file' => $file,
						'image' => '<img src="'.$boardurl.'/tp-files/tp-articles/illustrations/'.$file.'" alt="'.$file.'" />',
						'background' => $boardurl.'/tp-files/tp-articles/illustrations/'.$file,
					);
				$count++;
			}
		}
		closedir($handle);
	}
	sort($context['TPortal']['articons']['illustrations']);
}

function tp_recordevent($date, $id_member, $textvariable, $link, $description, $allowed, $eventid)
{
    $db = database();

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
}

function tp_fatal_error($error)
{
	global $context;

	$context['sub_template'] = 'tp_fatal_error';
	$context['TPortal']['errormessage'] = $error;
}

// Recent topic list:   [board] Subject by Poster	Date
function tp_recentTopics($num_recent = 8, $exclude_boards = null, $include_boards = null, $output_method = 'echo')
{
    return ssi_recentTopics($num_recent, $exclude_boards, $include_boards, $output_method);
}

// Download an attachment.
function tpattach()
{
	global $txt, $modSettings, $context;

    $db = database();

	// Some defaults that we need.
	$context['utf8'] = true;
	$context['no_last_modified'] = true;

	// Make sure some attachment was requested!
	if (!isset($_REQUEST['attach']) && !isset($_REQUEST['id']))
		fatal_lang_error('no_access', false);

	$_REQUEST['attach'] = isset($_REQUEST['attach']) ? (int) $_REQUEST['attach'] : (int) $_REQUEST['id'];

	if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'avatar')
	{
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
	else
	{
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
	if ($db->num_rows($request) == 0)
		fatal_lang_error('no_access', false);
	list ($id_folder, $real_filename, $file_hash, $file_ext, $id_attach, $attachment_type, $mime_type, $is_approved) = $db->fetch_row($request);
	$db->free_result($request);

	$filename = getAttachmentFilename($real_filename, $_REQUEST['attach'], $id_folder, false, $file_hash);

	// This is done to clear any output that was made before now. (would use ob_clean(), but that's PHP 4.2.0+...)
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']) && @version_compare(PHP_VERSION, '4.2.0') >= 0 && @filesize($filename) <= 4194304 && in_array($file_ext, array('txt', 'html', 'htm', 'js', 'doc', 'pdf', 'docx', 'rtf', 'css', 'php', 'log', 'xml', 'sql', 'c', 'java')))
		@ob_start('ob_gzhandler');
	else
	{
		ob_start();
		header('Content-Encoding: none');
	}

	// No point in a nicer message, because this is supposed to be an attachment anyway...
	if (!file_exists($filename))
	{
		loadLanguage('Errors');

		header('HTTP/1.0 404 ' . $txt['attachment_not_found']);
		header('Content-Type: text/plain; charset=UTF-8');

		// We need to die like this *before* we send any anti-caching headers as below.
		die('404 - ' . $txt['attachment_not_found']);
	}

	// If it hasn't been modified since the last time this attachement was retrieved, there's no need to display it again.
	if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
	{
		list($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
		if (strtotime($modified_since) >= filemtime($filename))
		{
			ob_end_clean();

			// Answer the question - no, it hasn't been modified ;).
			header('HTTP/1.1 304 Not Modified');
			exit;
		}
	}

	// Check whether the ETag was sent back, and cache based on that...
	$eTag = '"' . substr($_REQUEST['attach'] . $real_filename . filemtime($filename), 0, 64) . '"';
	if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $eTag) !== false)
	{
		ob_end_clean();

		header('HTTP/1.1 304 Not Modified');
		exit;
	}

	// Send the attachment headers.
	header('Pragma: ');

	if (!$context['browser']['is_gecko'])
		header('Content-Transfer-Encoding: binary');
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 525600 * 60) . ' GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
	header('Accept-Ranges: bytes');
	header('Set-Cookie:');
	header('Connection: close');
	header('ETag: ' . $eTag);

	// IE 6 just doesn't play nice. As dirty as this seems, it works.
	if ($context['browser']['is_ie6'] && isset($_REQUEST['image']))
		unset($_REQUEST['image']);

	elseif (filesize($filename) != 0)
	{
		$size = @getimagesize($filename);
		if (!empty($size))
		{
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
			if (!empty($size['mime']) && !in_array($size[2], array(4, 13)))
				header('Content-Type: ' . strtr($size['mime'], array('image/bmp' => 'image/x-ms-bmp')));
			elseif (isset($validTypes[$size[2]]))
				header('Content-Type: image/' . $validTypes[$size[2]]);
			// Otherwise - let's think safety first... it might not be an image...
			elseif (isset($_REQUEST['image']))
				unset($_REQUEST['image']);
		}
		// Once again - safe!
		elseif (isset($_REQUEST['image']))
			unset($_REQUEST['image']);
	}

	header('Content-Disposition: ' . (isset($_REQUEST['image']) ? 'inline' : 'attachment') . '; filename="' . $real_filename . '"');
	if (!isset($_REQUEST['image']))
		header('Content-Type: application/octet-stream');

	// If this has an "image extension" - but isn't actually an image - then ensure it isn't cached cause of silly IE.
	if (!isset($_REQUEST['image']) && in_array($file_ext, array('gif', 'jpg', 'bmp', 'png', 'jpeg', 'tiff')))
		header('Cache-Control: no-cache');
	else
		header('Cache-Control: max-age=' . (525600 * 60) . ', private');

	if (empty($modSettings['enableCompressedOutput']) || filesize($filename) > 4194304)
		header('Content-Length: ' . filesize($filename));

	// Try to buy some time...
	@set_time_limit(0);

	// Since we don't do output compression for files this large...
	if (filesize($filename) > 4194304)
	{
		// Forcibly end any output buffering going on.
		if (function_exists('ob_get_level'))
		{
			while (@ob_get_level() > 0)
				@ob_end_clean();
		}
		else
		{
			@ob_end_clean();
			@ob_end_clean();
			@ob_end_clean();
		}

		$fp = fopen($filename, 'rb');
		while (!feof($fp))
		{
			if (isset($callback))
				echo $callback(fread($fp, 8192));
			else
				echo fread($fp, 8192);
			flush();
		}
		fclose($fp);
	}
	// On some of the less-bright hosts, readfile() is disabled.  It's just a faster, more byte safe, version of what's in the if.
	elseif (isset($callback) || @readfile($filename) == null)
		echo isset($callback) ? $callback(file_get_contents($filename)) : file_get_contents($filename);

	obExit(false);
}

function art_recentitems($max = 5, $type = 'date' ){

    $db = database();

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
}

function TP_bbcbox($input) {{{
   echo'<div id="tp_smilebox"></div>';
   echo'<div id="tp_messbox"></div>';

   echo template_control_richedit($input, 'tp_messbox', 'tp_smilebox');
}}}

function TP_prebbcbox($id, $body = '') {{{
	require_once(SUBSDIR . '/Editor.subs.php');

	$editorOptions = array(
		'id' => $id,
		'value' => $body,
		'preview_type' => 2,
		'height' => '300px',
		'width' => '100%',
	);
	create_control_richedit($editorOptions);
}}}

function tp_getblockstyles() {{{
	return array(
		'0' => array(
			'class' => 'titlebg+content',
			'code_title_left' => '<div class="title_bar"><h3 class="category_header">',
			'code_title_right' => '</h3></div>',
			'code_top' => '<div class="content"><span class="topslice"><span></span></span><div style="padding: 0 8px;">',
			'code_bottom' => '</div><span class="botslice"><span></span></span></div>',
		),
		'1' => array(
			'class' => 'catbg+content',
			'code_title_left' => '<div class="cat_bar"><h3 class="category_header">',
			'code_title_right' => '</h3></div>',
			'code_top' => '<div class="content"><span class="topslice"><span></span></span><div style="padding: 0 8px;">',
			'code_bottom' => '</div><span class="botslice"><span></span></span></div>',
		),
		'2' => array(
			'class' => 'titlebg+content(old)',
			'code_title_left' => '<div class="title_bar"><h3 class="category_header">',
			'code_title_right' => '</h3></div>',
			'code_top' => '<div class="content"><div style="padding: 8px;">',
			'code_bottom' => '</div></div>',
		),
		'3' => array(
			'class' => 'catbg+content(old)',
			'code_title_left' => '<div class="cat_bar"><h3 class="category_header">',
			'code_title_right' => '</h3></div>',
			'code_top' => '<div class="content"><div style="padding: 8px;">',
			'code_bottom' => '</div></div>',
		),
		'4' => array(
			'class' => 'titlebg+content',
			'code_title_left' => '<div class="tp_half"><h3 class="category_header"><span class="l"></span><span class="r"></span>',
			'code_title_right' => '</h3></div>',
			'code_top' => '<div class="content"><div style="padding: 8px 8px 0 8px;">',
			'code_bottom' => '</div><span class="botslice"><span></span></span></div>',
		),
		'5' => array(
			'class' => 'catbg+content',
			'code_title_left' => '<div class="tp_half"><h3 class="category_header"><span class="l"></span><span class="r"></span>',
			'code_title_right' => '</h3></div>',
			'code_top' => '<div class="content"><div style="padding: 8px 8px 0 8px;">',
			'code_bottom' => '</div><span class="botslice"><span></span></span></div>',
		),
		'6' => array(
			'class' => 'titlebg+content',
			'code_title_left' => '<div class="tp_half"><h3 class="category_header"><span class="l"></span><span class="r"></span>',
			'code_title_right' => '</h3></div>',
			'code_top' => '<div class="content"><div style="padding: 8px 8px 0 8px;">',
			'code_bottom' => '</div><span class="botslice"><span></span></span></div>',
		),
		'7' => array(
			'class' => 'catbg+content',
			'code_title_left' => '<div class="tp_half"><h3 class="category_header"><span class="l"></span><span class="r"></span>',
			'code_title_right' => '</h3></div>',
			'code_top' => '<div class="content"><div style="padding: 8px 8px 0 8px;">',
			'code_bottom' => '</div><span class="botslice"><span></span></span></div>',
		),
		'8' => array(
			'class' => 'titlebg+roundframe',
			'code_title_left' => '<div class="tp_half"><h3 class="category_header"><span class="l"></span><span class="r"></span>',
			'code_title_right' => '</h3></div>',
			'code_top' => '<div class="roundframe"><div style="padding: 8px 0 0 0px;">',
			'code_bottom' => '</div></div><span class="lowerframe"><span></span></span>',
		),
		'9' => array(
			'class' => 'catbg+roundframe',
			'code_title_left' => '<div class="tp_half"><h3 class="category_header"><span class="l"></span><span class="r"></span>',
			'code_title_right' => '</h3></div>',
			'code_top' => '<div class="roundframe"><div style="padding: 8px 0px 0 0;">',
			'code_bottom' => '</div></div><span class="lowerframe"><span></span></span>',
		),
	);
}}}

function tp_getblockstyles21() {{{
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

function get_grps($save = true, $noposts = true) {{{
	global $context, $txt;

    $db = database();

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

function tp_convertphp($code, $reverse = false) {{{

	if(!$reverse) {
		return $code;
	}
	else {
		return $code;
	}
}}}

function updateTPSettings($addSettings, $check = false) {{{
	global $context;

    $db = database();

	if (empty($addSettings) || !is_array($addSettings))
		return;

	if($check)
	{
		foreach ($addSettings as $variable => $value)
		{
			$request = $db->query('', 'SELECT value FROM {db_prefix}tp_settings WHERE name = \'' . $variable . '\'');

			if($db->num_rows($request)==0)
			{
				$db->query('', '
					INSERT INTO {db_prefix}tp_settings
					(name,value) VALUES({string:variable},{' . ($value === false || $value === true ? 'raw' : 'string') . ':value})',
					array(
						'value' => $value === true ? 'value + 1' : ($value === false ? 'value - 1' : $value),
						'variable' => $variable,
					)
				);
			}
			$db->query('', '
					UPDATE {db_prefix}tp_settings
					SET value = {' . ($value === false || $value === true ? 'raw' : 'string') . ':value}
					WHERE name = {string:variable}',
					array(
						'value' => $value === true ? 'value + 1' : ($value === false ? 'value - 1' : $value),
						'variable' => $variable,
					)
				);

			$context['TPortal'][$variable] = $value === true ? $context['TPortal'][$variable] + 1 : ($value === false ? $context['TPortal'][$variable] - 1 : $value);
		}
	}
	else
	{
		foreach ($addSettings as $variable => $value)
		{
			$db->query('', '
				UPDATE {db_prefix}tp_settings
				SET value = {' . ($value === false || $value === true ? 'raw' : 'string') . ':value}
				WHERE name = {string:variable}',
				array(
					'value' => $value === true ? 'value + 1' : ($value === false ? 'value - 1' : $value),
					'variable' => $variable,
				)
			);
			$context['TPortal'][$variable] = $value === true ? $context['TPortal'][$variable] + 1 : ($value === false ? $context['TPortal'][$variable] - 1 : $value);
		}
	}
	// Clean out the cache and make sure the cobwebs are gone too.
	cache_put_data('tpSettings', null, 90);

	return;
}}}

function TPGetMemberColour($member_ids) {{{
	if (empty($member_ids)) {
		return false;
    }

    $db = database();

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
function tp_profile_summary($member_id) {{{
	global $txt, $context;
	$context['page_title'] = $txt['tpsummary'];
	// get all articles written by member
    $max_art = TPArticle::getInstance()->getTotalAuthorArticles($member_id);
	$context['TPortal']['tpsummary']=array(
		'articles' => $max_art,
	);
}}}

// articles and comments made by the member
function tp_profile_articles($member_id) {{{
	global $txt, $context, $scripturl;

    $db = database();

	$context['page_title'] = $txt['articlesprofile'];
    $context['TPortal']['member_id'] = $member_id;

    $tpArticle  = TPArticle::getInstance();
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
					'editlink' => allowedTo('tp_articles') ? $scripturl.'?action=admin;area=tpadmin;sa=editarticle'.$row['id'] : $scripturl.'?action=tportal;sa=editarticle'.$row['id'],
				);
            }
		}
		$db->free_result($request);
	}
	
    // construct pageindexes
	$context['TPortal']['pageindex'] = '';
	if($max > 0) {
		$context['TPortal']['pageindex'] = TPageIndex($scripturl.'?action=profile;area=tpadmin;sa=tparticles;u='.$member_id.';tpsort='.$sorting, $start, $max, '15');
    }

	// setup subaction
	$context['TPortal']['profile_action'] = '';
	if(isset($_GET['sa']) && $_GET['sa'] == 'settings') {
		$context['TPortal']['profile_action'] = 'settings';
    }
	
	// Create the tabs for the template.
	$context[$context['profile_menu_name']]['tab_data'] = array(
		'title' => $txt['articlesprofile'],
		'description' => $txt['articlesprofile2'],
		'tabs' => array(
			'articles' => array(),
			'settings' => array(),
			),
	);
	// setup values for personal settings - for now only editor choice
	// type = 1 -
	// type = 2 - editor choice
	$result = $db->query('', '
		SELECT id, value FROM {db_prefix}tp_data
		WHERE type = {int:type} AND id_member = {int:id_mem} LIMIT 1',
		array('type' => 2, 'id_mem' => $member_id)
	);
	if($db->num_rows($result) > 0) {
		$row = $db->fetch_assoc($result);
		$context['TPortal']['selected_member_choice'] = $row['value'];
		$context['TPortal']['selected_member_choice_id'] = $row['id'];
		$db->free_result($result);
	}
	else {
		$context['TPortal']['selected_member_choice'] = 0;
		$context['TPortal']['selected_member_choice_id'] = 0;
	}
	
    $context['TPortal']['selected_member'] = $member_id;
	if(loadLanguage('TPortalAdmin') == false) {
		loadLanguage('TPortalAdmin', 'english');
    }

}}}

// Tinyportal
function tp_summary($member_id) {{{
	global $txt, $context;
	loadtemplate('TPprofile');
	$context['page_title'] = $txt['tpsummary'];
	tp_profile_summary($member_id);
}}}

function tp_articles($member_id) {{{
	global $txt, $context;
	TPArticleCategories();
	loadtemplate('TPprofile');
	$context['page_title'] = $txt['articlesprofile'];
	tp_profile_articles($member_id);
}}}

function TPSaveSettings() {{{

    $db = database();

    // check the session
    checkSession('post');
    $member_id  = TPUtil::filter('memberid', 'post', 'int');
    $item       = TPUtil::filter('item', 'post', 'int');
    $value      = TPUtil::filter('tpwysiwyg', 'post', 'int');
    if( $value !== false ) {
        if( $item > 0 ) {
            $db->query('', '
                UPDATE {db_prefix}tp_data
                SET value = {int:val} WHERE id = {int:id}',
                array('val' => $value, 'id' => $item)
            );
        }
        elseif ($member_id != false) {
            $db->insert('INSERT',
                '{db_prefix}tp_data',
                array('type' => 'int', 'id_member' => 'int', 'value' => 'int'),
                array(2, $member_id, $value),
                array('id')
            );
        }
    }

    // go back to profile page
    redirectexit('action=profile;u='.$member_id.';area=tpadmin;sa=tparticles;sa=settings');

}}}

function TPUpdateLog() {{{
    global $context;

    $db = database();

    $context['TPortal']['subaction'] = 'updatelog';
    $request = $db->query('', '
        SELECT value1 FROM {db_prefix}tp_variables
        WHERE type = {string:type} ORDER BY id DESC',
        array('type' => 'updatelog')
    );
    if($db->num_rows($request) > 0) {
        $check = $db->fetch_assoc($request);
        $context['TPortal']['updatelog'] = $check['value1'];
        $db->free_result($request);
    }
    else {
        $context['TPortal']['updatelog'] = "";
    }
    loadtemplate('TPsubs');
    $context['sub_template'] = 'updatelog';

}}}

if (!function_exists('is_countable')) {
    function is_countable($var) {
        return ( is_array($var) || $var instanceof Countable || $var instanceof \SimpleXMLElement || $var instanceof \ResourceBundle );
    }
}

?>
