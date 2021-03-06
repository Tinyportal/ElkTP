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

class User extends Base
{

    public function __construct() {{{
        parent::__construct();

    }}}

    public function setup( &$block ) {{{

        if(!$this->context['user']['is_logged']) {
            $block['title'] = '<a class="subject"  href="'.$this->this->scripturl.'?action=login">'.$block['title'].'</a>';
        }

    }}}

    function display( $block ) {{{

        $bullet = '<img src="'.$this->settings['tp_images_url'].'/TPdivider.png" alt="" style="margin:0 4px 0 0;" />';
        $bullet2 = '<img src="'.$this->settings['tp_images_url'].'/TPdivider2.png" alt="" style="margin:0 4px 0 0;" />';
        $bullet3 = '<img src="'.$this->settings['tp_images_url'].'/TPdivider3.png" alt="" style="margin:0 4px 0 0;" />';
        $bullet4 = '<img src="'.$this->settings['tp_images_url'].'/TPmodule2.png" alt="" style="margin:0 4px 0 0;" />';
        $bullet5 = '<img src="'.$this->settings['tp_images_url'].'/TPmodule2.png" alt=""  style="margin:0 4px 0 0;" />';

        echo'
        <div class="tp_userblocknew">';


        // If the user is logged in, display stuff like their name, new messages, etc.

        if ($this->context['user']['is_logged']) {

            if (!empty($this->context['user']['avatar']) &&  isset($this->context['TPortal']['userbox']['avatar'])) {
                echo '
                    <span class="tpavatar">', $this->context['user']['avatar']['image'], '</span>';
            }

            echo '
            <strong><a class="subject"  href="'.$this->scripturl.'?action=profile;u='.$this->context['user']['id'].'">', $this->context['user']['name'], '</a></strong>
            <ul class="reset">';

            // Only tell them about their messages if they can read their messages!
            if ($this->context['allow_pm']) {
                echo '
                <li><a href="', $this->scripturl, '?action=pm">' .$bullet.$this->txt['tp-pm'].' ',  $this->context['user']['messages'], '</a></li>';
                if($this->context['user']['unread_messages'] > 0)
                    echo '
                <li style="font-weight: bold; "><a href="', $this->scripturl, '?action=pm">' . $bullet. $this->txt['tp-pm2'].' ',$this->context['user']['unread_messages'] , '</a></li>';
            }
            // Are there any members waiting for approval?
            if (!empty($this->context['unapproved_members'])) {
                echo '<li><a href="', $this->scripturl, '?action=admin;area=viewmembers;sa=browse;type=approve;' . $this->context['session_var'] . '=' . $this->context['session_id'].'">'. $bullet. $this->txt['tp_unapproved_members'].' '. $this->context['unapproved_members']  . '</a></li>';
            }
            // Are there any moderation reports?
            if (!empty($this->context['open_mod_reports']) && $this->context['show_open_reports']) {
                echo '
                    <li><a href="', $this->scripturl, '?action=moderate;area=reports">'.$bullet.$this->txt['tp_modreports'].' ' . $this->context['open_mod_reports']. '</a></li>';
            }

            if(isset($this->context['TPortal']['userbox']['unread'])) {
                echo '<li><hr><a href="', $this->scripturl, '?action=unread">' .$bullet.$this->txt['tp-unread'].'</a></li>
                <li><a href="', $this->scripturl, '?action=unreadreplies">'.$bullet.$this->txt['tp-replies'].'</a></li>
                <li><a href="', $this->scripturl, '?action=profile;u='.$this->context['user']['id'].';area=showposts">'.$bullet.$this->txt['tp-showownposts'].'</a></li>
                <li><a href="', $this->scripturl, '?action=tparticle;sa=showcomments">'.$bullet.$this->txt['tp-showcomments'].'</a><hr></li>
                ';
            }

            // Is the forum in this->maintenance mode?
            if ($this->maintenance && $this->context['user']['is_admin']) {
                echo '<li>' .$bullet2.$this->txt['tp_maintenace']. '</li>';
            }
            // Show the total time logged in?
            if (!empty($this->context['user']['total_time_logged_in']) && isset($this->context['TPortal']['userbox']['logged'])) {
                echo '<li>' .$bullet2.$this->txt['tp-loggedintime'] . '</li>
                <li>'.$bullet2.$this->context['user']['total_time_logged_in']['days'] . $this->txt['tp-acronymdays']. $this->context['user']['total_time_logged_in']['hours'] . $this->txt['tp-acronymhours']. $this->context['user']['total_time_logged_in']['minutes'] .$this->txt['tp-acronymminutes'].'</li>';
            }
            if (isset($this->context['TPortal']['userbox']['time'])) {
                echo '<li>' . $bullet2.$this->context['current_time'].' <hr></li>';
            }

            // admin parts etc.
             if(!isset($this->context['TPortal']['can_submit_article'])) {
                $this->context['TPortal']['can_submit_article']=0;
            }

             // can we submit an article?
             if(allowedTo('tp_submithtml')) {
                 echo '<li><a href="', $this->scripturl, '?action=admin;area=tparticles;sa=addarticle_html">' . $bullet3.$this->txt['tp-submitarticle']. '</a></li>';
             }

            if(allowedTo('tp_submitbbc')) {
                echo '<li><a href="', $this->scripturl, '?action=admin;area=tparticles;sa=addarticle_bbc">' . $bullet3.$this->txt['tp-submitarticlebbc']. '</a></li>';
            }

            if(allowedTo('tp_editownarticle')) {
                echo '<li><a href="', $this->scripturl, '?action=admin;area=tparticles;sa=myarticles">' . $bullet3.$this->txt['tp-myarticles']. '</a></li>';
            }

            // tpadmin checks
            if (allowedTo('tp_this->settings')) {
                echo '<li><hr><a href="' . $this->scripturl . '?action=admin;area=tparticles;sa=this->settings">' . $bullet4.$this->txt['permissionname_tp_settings'] . '</a></li>';
            }
            
            if (allowedTo('tp_blocks')) {
                echo '<li><a href="' . $this->scripturl . '?action=admin;area=tparticles;sa=blocks">' . $bullet4.$this->txt['permissionname_tp_blocks'] . '</a></li>';
            }

            if (allowedTo('tp_articles')) {
                echo '<li><a href="' . $this->scripturl . '?action=admin;area=tparticles;sa=articles">' . $bullet4.$this->txt['permissionname_tp_articles'] . '</a></li>';
            }
                    echo '
            </ul>';
        }
        // Otherwise they're a guest - so politely ask them to register or login.
        else  {
            if (!empty($this->modSettings['registration_method']) && $this->modSettings['registration_method'] == 1) {
                $this->txt['welcome_guest'] .= $this->txt['welcome_guest_activate'];
            }

            $this->txt['welcome_guest'] = replaceBasicActionUrl($this->txt['welcome_guest']);

            echo '<div style="line-height: 1.4em;">', $this->txt['welcome_guest'], '</div>';
        echo '
            <form style="margin-top: 5px;" action="', $this->scripturl, '?action=login2" method="post" >
                <input type="text" class="input_text" name="user" size="10" style="max-width: 45%!important;"/> <input type="password" class="input_password" name="passwrd" size="10" style="max-width: 45%!important;"/><br>
                <select name="cookielength" style="max-width: 45%!important;">
                    <option value="60">', $this->txt['one_hour'], '</option>
                    <option value="1440">', $this->txt['one_day'], '</option>
                    <option value="10080">', $this->txt['one_week'], '</option>
                    <option value="302400">', $this->txt['one_month'], '</option>
                    <option value="-1" selected="selected">', $this->txt['forever'], '</option>
                </select>
                <input type="submit" class="button_submit" value="', $this->txt['login'], '" />
                <input type="hidden" name="', $this->context['session_var'], '" value="', $this->context['session_id'], '" />
                <input type="hidden" name="', $this->context['login_token_var'], '" value="', $this->context['login_token'], '">
            </form>
            <div style="line-height: 1.4em;" class="middletext">', $this->txt['quick_login_dec'], '</div>';
        }
        echo '
        </div>';


    }}}

}

?>
