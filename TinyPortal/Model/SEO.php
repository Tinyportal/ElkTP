<?php
/**
 * @package TinyPortal
 * @version 1.0.0 RC2
 * @author TinyPortal - http://www.tinyportal.net
 * @license BSD 3.0 http://opensource.org/licenses/BSD-3-Clause/
 *
 * Copyright - The TinyPortal Team
 *
 */
namespace TinyPortal\Model;

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class SEO {

    private static $latinCharMap = array(
        'A'  => array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ā', 'Ă', 'Ą', 'Ǎ', 'Ǻ'),
        'AE' => array('Æ', 'Ǽ'),
        'C'  => array('Ç', 'Ć', 'Ċ', 'Ĉ', 'Č'),
        'D'  => array('Ð', 'Ď', 'Đ'),
        'E'  => array('È', 'É', 'Ê', 'Ë', 'Ē', 'Ĕ', 'Ė', 'Ę', 'Ě'),
        'F'  => array('ƒ'),
        'G'  => array('Ĝ', 'Ğ', 'Ġ', 'Ģ'),
        'H'  => array('Ĥ', 'Ħ'),
        'I'  => array('Ì', 'Í', 'Î', 'Ï', 'Ĩ', 'Ī', 'Ĭ', 'Į', 'İ', 'Ǐ'),
        'IJ' => array('Ĳ'),
        'J'  => array('Ĵ'),
        'K'  => array('Ķ'),
        'L'  => array('Ĺ', 'Ļ', 'Ľ', 'Ŀ', 'Ł'),
        'N'  => array('Ñ', 'Ń', 'Ņ', 'Ň', 'ŉ'),
        'O'  => array('Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ō', 'Ŏ', 'Ő', 'Ơ', 'Ǒ', 'Ǿ', 'Ø'),
        'OE' => array('Œ'),
        'R'  => array('Ŕ', 'Ŗ', 'Ř'),
        'S'  => array('Ś', 'Ŝ', 'Ş', 'Š', 'ſ'),
        'SS' => array('ß'),
        'T'  => array('Ţ', 'Ť', 'Ŧ'),
        'U'  => array('Ù', 'Ú', 'Û', 'Ü', 'Ũ', 'Ū', 'Ŭ', 'Ů', 'Ű', 'Ų', 'Ư', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ'),
        'W'  => array('Ŵ'),
        'Y'  => array('Ý', 'Ŷ', 'Ÿ'),
        'Z'  => array('Ź', 'Ż', 'Ž'),
    );

    public static function generateUrlString($params = array(), $convertToLatin = true, $lowerCase = true) {{{

        global $boardurl, $scripturl, $modSettings;

        if($convertToLatin == true) {
            foreach (YAPortalSEO::$latinCharMap as $to => $from) {
                $params = preg_replace("/(" . implode('|', $from) . ")/u", $to, $params);

                foreach ($from as &$value) {
                    $value = mb_strtolower($value, 'UTF-8');
                }

                $params = preg_replace("/(" . implode('|', $from) . ")/u", strtolower($to), $params);
            }
        }

        if ($lowerCase == true) {
            $params = array_map('strtolower', $params);
        }

        // replace non-latin letters to "-"
        $params     = preg_replace('/[^A-Za-z0-9]+/', '-', $params);
        // remove "-" from the beginning and end
        $params     = preg_replace('/(^[-]+)|([.]*[-]$)/', '', $params);

        if(!empty($modSettings['yaportal-seo'])) {
            $urlString  = implode('/', array_values($params));
            $urlString  .= '/';
        }
        else {
            $urlString  = http_build_query($params);
        }

        if(!empty($urlString)) {
            if(!empty($modSettings['yaportal-seo-strip-index'])) {
                return parse_url($scripturl, PHP_URL_SCHEME).'://'.parse_url($scripturl, PHP_URL_HOST).'/'.$urlString;
            } 
            else {
                return $scripturl.'?'.$urlString;
            }
        }
        else {
            return false;
        }

    }}}

}
