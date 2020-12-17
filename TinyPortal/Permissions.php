<?php
/**
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
namespace TinyPortal;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class Permissions 
{

    public static function checkAdminAreas() {{{
        global $context;

        self::collectPermissions();
        foreach($context['TPortal']['adminlist'] as $adm => $val) {
            if(\allowedTo($adm) || !empty($context['TPortal']['show_download'])) {
                return true;
            }
        }
        return false;

    }}}

    public static function collectPermissions() {{{
        global $context;

        $context['TPortal']['permissonlist'] = array();
        // first, the built-in permissions
        $context['TPortal']['permissonlist'][] = array(
            'title' => 'tinyportal',
            'perms' => array(
                'tp_settings' => 0,
                'tp_blocks' => 0,
                'tp_articles' => 0
            )
        );
        $context['TPortal']['permissonlist'][] = array(
            'title' => 'tinyportal_dl',
            'perms' => array(
                'tp_dlmanager' => 0,
                'tp_dlupload' => 0
            )
        );
        $context['TPortal']['permissonlist'][] = array(
            'title' => 'tinyportal_submit',
            'perms' => array(
                'tp_submithtml' => 0,
                'tp_submitbbc' => 0,
                'tp_editownarticle' => 0,
                'tp_alwaysapproved' => 0
            )
        );

        $context['TPortal']['tppermissonlist'] = array(
            'tp_settings' => array(false, 'tinyportal', 'tinyportal'),
            'tp_blocks' => array(false, 'tinyportal', 'tinyportal'),
            'tp_articles' => array(false, 'tinyportal', 'tinyportal'),
            'tp_submithtml' => array(false, 'tinyportal', 'tinyportal'),
            'tp_submitbbc' => array(false, 'tinyportal', 'tinyportal'),
            'tp_editownarticle' => array(false, 'tinyportal', 'tinyportal'),
            'tp_alwaysapproved' => array(false, 'tinyportal', 'tinyportal'),
            'tp_dlmanager' => array(false, 'tinyportal', 'tinyportal'),
            'tp_dlupload' => array(false, 'tinyportal', 'tinyportal')
        );

        $context['TPortal']['adminlist'] = array(
            'tp_settings' => 1,
            'tp_blocks' => 1,
            'tp_articles' => 1,
            'tp_dlmanager' => 1,
            'tp_submithtml' => 1,
            'tp_submitbbc' => 1,
        );
    }}}

    public static function getButtons() {{{
        global $scripturl, $txt, $context;

        if(loadLanguage('TPortal') == false) {
            loadLanguage('TPortal', 'english');
        }

        $buts = array();

        if($context['user']['is_logged'] && (allowedTo('tp_submithtml') || allowedTo('tp_submitbbc') || allowedTo('tp_articles'))) {
            $buts['tpeditwonarticle'] = array(
                'title' => $txt['tp-myarticles'],
                'href' => $scripturl . '?action=tportal;sa=myarticles',
                'show' => true,
                'active_button' => false,
                'sub_buttons' => array(),
            );
        }

        if(allowedTo('tp_submithtml') || allowedTo('tp_articles')) {
            $buts['tpeditwonarticle']['sub_buttons']['submithtml'] = array(
                'title' => $txt['tp-submitarticle'],
                'href' => $scripturl . '?action=' . (allowedTo('tp_articles') ? 'tpadmin' : 'tportal') . ';sa=addarticle_html',
                'show' => true,
                'active_button' => false,
                'sub_buttons' => array(),
            );
        }

        if(allowedTo('tp_submitbbc') || allowedTo('tp_articles')) {
            $buts['tpeditwonarticle']['sub_buttons']['submitbbc'] = array(
                'title' => $txt['tp-submitarticlebbc'],
                'href' => $scripturl . '?action=' . (allowedTo('tp_articles') ? 'tpadmin' : 'tportal') . ';sa=addarticle_bbc',
                'show' => true,
                'active_button' => false,
                'sub_buttons' => array(),
            );
        }

        // the admin functions - divider
        if(allowedTo('tp_settings') || allowedTo('tp_articles') || allowedTo('tp_blocks') || allowedTo('tp_dlmanager') || allowedTo('tp_shoutbox')) {
            $buts['divde1'] = array(
                'title' => '<hr />',
                'href' => '#',
                'show' => true,
                'active_button' => false,
                'sub_buttons' => array(),
            );
        }

        if(allowedTo('tp_settings')) {
            $buts['tpsettings'] = array(
                'title' => $txt['tp-adminheader1'],
                'href' => $scripturl . '?action=tpadmin;sa=settings',
                'show' => true,
                'active_button' => false,
                'sub_buttons' => array(),
            );
        }

        if(allowedTo('tp_articles')) {
            $buts['tparticles'] = array(
                'title' => $txt['tp_menuarticles'],
                'href' => $scripturl . '?action=tpadmin;sa=articles',
                'show' => true,
                'active_button' => false,
                'sub_buttons' => array(),
            );
        }

        if(allowedTo('tp_blocks')) {
            $buts['tpblocks'] = array(
                'title' => $txt['tp-adminpanels'],
                'href' => $scripturl . '?action=tpadmin;sa=blocks',
                'show' => true,
                'active_button' => false,
                'sub_buttons' => array(),
            );
        }

        return $buts;
    }}}

}

?>
