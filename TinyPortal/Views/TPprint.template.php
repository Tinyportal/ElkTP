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

function template_tp_print_above()
{
	global $context, $settings, $txt;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', 'UTF-8', '" />
		<title>', $txt['print_page'], ' - ', $context['page_title'] , '</title>
		<style type="text/css">
			body
			{
				color: black;
				background-color: white;
				padding: 12px 5% 10px 5%;
			}
			body, td, .normaltext
			{
				font-family: Verdana, arial, helvetica, serif;
				font-size: small;
			}
			*, a:link, a:visited, a:hover, a:active
			{
				color: black !important;
			}
			table
			{
				empty-cells: show;
			}
			.code
			{
				font-size: x-small;
				font-family: monospace;
				border: 1px solid black;
				margin: 1px;
				padding: 1px;
			}
			.quote
			{
				font-size: x-small;
				border: 1px solid black;
				margin: 1px;
				padding: 1px;
			}
			.smalltext, .quoteheader, .codeheader
			{
				font-size: x-small;
			}
			.largetext
			{
				font-size: large;
			}
			hr
			{
				height: 1px;
				border: 0;
				color: black;
				background-color: black;
			}
		</style>';

	echo '
		<link rel="stylesheet" type="text/css" href="TinyPortal/Views/css/tp-style.css?"'.TPVERSION.' />
		<link rel="stylesheet" type="text/css" href="TinyPortal/Views/css/tp-style.css?"'.TPVERSION.' />';

	echo '
	</head>
	<body>';
}

function template_tp_print_body()
{
	global $context;

	echo $context['TPortal']['printbody'];
}

function template_tp_print_below()
{
	global $context;

	echo '
			<br>
			<div class="smalltext tpcenter">', theme_copyright(), '
				<p>' , $context['TPortal']['print'] , '</p>
			</div>
	</body>
</html>';
}
?>
