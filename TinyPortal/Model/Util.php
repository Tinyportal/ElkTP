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

// Static method to call Database or Util calls which are not database related.
class Util
{

	public static function __callStatic($call, $vars) {{{

        return call_user_func_array("\ElkArte\Util::$call", $vars);

	}}}

    public static function find_in_set($data, $field, $arg = 'OR') {{{

        $dB = Database::getInstance();

        if( ($arg != "OR") && ($arg != "AND") ) {
            return;
        }

		array_walk($data, function (&$value, $key) use ($dB) {
				$value = $dB->db_quote('{string:value}', array( 'value' => $value));
			}
		);

		$str = '';
		if(TP_PGSQL == false) {
			if($arg == 'OR') {
				$str = '(FIND_IN_SET(' . implode(', '.$field.') '.$arg.' FIND_IN_SET(', $data) . ', '.$field.'))';
			}
			else {
				$str = 'AND (FIND_IN_SET(' . implode(', '.$field.') OR FIND_IN_SET(', $data) . ', '.$field.'))';
			}
		}
		else {
			if($arg == 'OR') {
				foreach($data as $k => $v) {
					$str .= ' '.$v.' = ANY (string_to_array('.$field.', \',\' ) ) '.$arg.' ';
				}
				$str = rtrim($str,' '.$arg.' ');
			}
			else {
				$str = 'AND ( '. implode(' = ANY (string_to_array( '.$field.', \',\' )) OR ', $data) . ' = ANY (string_to_array('.$field.', \',\')))';
			}
		}

        return $str;

    }}}

    public static function http_parse_query($queryString, $argSeparator = '&', $decType = PHP_QUERY_RFC1738) {{{
        $result             = array();
        $parts              = explode($argSeparator, $queryString);

        foreach ($parts as $part) {
            list($paramName, $paramValue)   = explode('=', $part, 2);

            switch ($decType) {
                case PHP_QUERY_RFC3986:
                    $paramName      = rawurldecode($paramName);
                    $paramValue     = rawurldecode($paramValue);
                    break;

                case PHP_QUERY_RFC1738:
                default:
                    $paramName      = urldecode($paramName);
                    $paramValue     = urldecode($paramValue);
                    break;
            }


            if (preg_match_all('/\[([^\]]*)\]/m', $paramName, $matches)) {
                $paramName      = substr($paramName, 0, strpos($paramName, '['));
                $keys           = array_merge(array($paramName), $matches[1]);
            }
            else {
                $keys           = array($paramName);
            }

            $target             = &$result;

            foreach ($keys as $index) {
                if ($index === '') {
                    if (isset($target)) {
                        if (is_array($target)) {
                            $intKeys    = array_filter(array_keys($target), 'is_int');
                            $index      = count($intKeys) ? max($intKeys)+1 : 0;
                        }
                        else {
                            $target     = array($target);
                            $index      = 1;
                        }
                    }
                    else {
                        $target         = array();
                        $index          = 0;
                    }
                }
                elseif (isset($target[$index]) && !is_array($target[$index])) {
                    $target[$index] = array($target[$index]);
                }

                $target         = &$target[$index];
            }

            if (is_array($target)) {
                $target[]   = $paramValue;
            }
            else {
                $target     = $paramValue;
            }
        }

        return $result;

    }}}

    public static function checkboxChecked($checkbox) {{{

        return self::filter($checkbox, 'post', 'string');

    }}}

    public static function shortenString(&$string, $length) {{{

        $shorten = FALSE;

        if(!empty($length)) {
            // Remove all the entities and change them to a space..
            $string     = preg_replace('/&nbsp;|&zwnj;|&raquo;|&laquo;|&gt;/', ' ', $string);

            if( self::strlen($string) > $length ) {
                $shorten    = TRUE;
                // Now we can find the closest space character
                $cutOffPos  = max(mb_strpos($string, ' ', $length), mb_strpos($string, '>', $length));
                if($cutOffPos !== false) {
                    $tmpString  = self::substr($string, 0, $cutOffPos);

                    // Find all the bbc tags then loop through finding the closing one
                    if(preg_match_all('/\[([a-zA-Z0-9_\-]+?)\]/', $tmpString, $matches) > 0 ) {
                        foreach($matches[1] as $key) {
                            // check we haven't cut any bbcode off
                            if(preg_match_all('/\[(['.$key.']+?)\](.+?)\[\/\1\]/', $tmpString, $match, PREG_SET_ORDER) == 0 ) {
                                // Search from the old cut off position to the next similar tag
                                $cutOffPos  = mb_strpos($string, '[/'.$key.']', $cutOffPos);
                                if($cutOffPos !== false) {
                                    $tmpString  = self::substr($string, 0, $cutOffPos);
                                }
                            }
                        }
                    }

                    // check that no html has been cut off
                    if(self::isHTML($string)) {
                        // Change all the new lines to <br>
                        $string         = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
                        $string         = str_ireplace(array("<br />","<br>","<br/>","<br />","&lt;br /&gt;","&lt;br/&gt;","&lt;br&gt;"), '<br>', $string);
                        $string         = mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');

                        $reachedLimit   = false;
                        $totalLen       = 0;
                        $toRemove       = array();

                        $dom = new \DomDocument('1.0', 'UTF-8');

						// set error level
						$internalErrors = libxml_use_internal_errors(true);

                        $dom->loadHTML($string);

						// Restore error level
						libxml_use_internal_errors($internalErrors);

                        self::walkHTML($dom, $length, $reachedLimit, $totalLen, $toRemove);

                        foreach ($toRemove as $child) {
                            $child->parentNode->removeChild($child);
                        }

                        $tmpString = $dom->saveHTML();

                        // Strip out the doctype and html body
                        if(($pos = mb_strpos($tmpString, '<html><body>')) !== FALSE) {
                            $tmpString = mb_substr($tmpString, $pos + 12);
                        }

                        // Remove the html body from the end
                        if(($pos = mb_strpos($tmpString, '</body></html>')) != FALSE) {
                            $tmpString = mb_substr($tmpString, 0, $pos);
                        }
                    }

                    // Assign it back to the string
                    $string = $tmpString;
                }
            }
        }

        return $shorten;

    }}}

    public static function walkHTML(\DomNode $node, $length, &$reachedLimit, &$totalLen, &$toRemove) {{{

        if($reachedLimit == true) {
            $toRemove[] = $node;
        }
        else {
            if($node instanceof \DomText) {
                $nodeLen    = mb_strlen($node->nodeValue);
                $totalLen   += $nodeLen;

                if($totalLen > $length) {
                    $node->nodeValue    = mb_substr($node->nodeValue, 0, mb_strpos($node->nodeValue, ' ', ($nodeLen - ($totalLen - $length))));
                    $reachedLimit       = true;
                }
            }

            if(isset($node->childNodes)) {
                foreach ($node->childNodes as $child) {
                    self::walkHTML($child, $length, $reachedLimit, $totalLen, $toRemove);
                }
            }
        }

        return;
    }}}

    public static function parseBBC($string) {{{

        if(preg_match_all('/\[([a-zA-Z=0-9_\-]+?)\](.+?)\[\/\1\]/', $string, $matches) > 0 ) {
            return $matches;
        }

        return false;

    }}}

    public static function isHTML( $string ) {{{

        // Remove any HTML which might be in bbc html tags for this check, this means bbc with html will break the shortenString function
        $string = preg_replace('/\[([html]+?)\](.+?)\[\/\1\]/', '', $string);

        return preg_match("~\/[a-z]*>~i", $string ) != 0;
    }}}

    public static function hasLinks($string) {{{

        if(empty($string)) {
            return false;
        }

        $pattern = '%^((https?://)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i';
        if (preg_match_all($pattern, $string, $matches, PREG_PATTERN_ORDER)) {
            return true;
        }

        return false;

    }}}

    public static function xssClean( $string ) {{{

        // URL decode
        $string = urldecode($string);
        // Convert Hexadecimals
        $string = preg_replace_callback('!(&#|\\\)[xX]([0-9a-fA-F]+);?!', function($m) {
            return chr(hexdec($m[2]));
        }, $string);
        // Clean up entities
        $string = preg_replace('!(&#0+[0-9]+)!','$1;',$string);
        // Decode entities
        $string = html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
        // Strip whitespace characters
        $string = preg_replace('!\s!','',$string);
        // Set the patterns we'll test against
        $patterns = array(
            // Match any attribute starting with "on" or xmlns
            '#(<[^>]+[\x00-\x20\"\'\/])(on|xmlns)[^>]*>?#iUu',
            // Match javascript:, livescript:, vbscript: and mocha: protocols
            '!((java|live|vb)script|mocha|feed|data):(\w)*!iUu',
            '#-moz-binding[\x00-\x20]*:#u',
            // Match style attributes
            '#(<[^>]+[\x00-\x20\"\'\/])style=[^>]*>?#iUu',
            // Match unneeded tags
            '#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>?#i'
        );

        foreach($patterns as $pattern) {
            $string = preg_replace($pattern, '', $string);
        }

        if(!empty($string)) {
            return $string;
        }
        else {
            return false;
        }

    }}}

    public static function filter($key, $type, $filterType = 'string', $options = array()) {{{

        switch($type) {
            case 'get':
                $data = $_GET;
                break;
            case 'post':
                $data = $_POST;
                break;
            case 'request':
                $data = $_REQUEST;
                break;
            default:
                return false;
                break;
        }

        if(!array_key_exists($key, $data)) {
            return false;
        }

        return filter_var($data[$key], self::filterType($filterType), $options);
    }}}

    private static function filterType($type) {{{
        switch (strtolower($type)) {
            case 'string':
                $filter = FILTER_UNSAFE_RAW;
                break;
            case 'int':
                $filter = FILTER_SANITIZE_NUMBER_INT;
                break;
            case 'float' || 'decimal':
                $filter = FILTER_SANITIZE_NUMBER_FLOAT;
                break;
            case 'encoded':
                $filter = FILTER_SANITIZE_ENCODED;
                break;
            case 'url':
                $filter = FILTER_SANITIZE_URL;
                break;
            case 'email':
                $filter = FILTER_SANITIZE_EMAIL;
                break;
            default:
                $filter = FILTER_UNSAFE_RAW;
        }
        return $filter;
    }}}

	private static function filter_string_polyfill(string $string): string {{{
	   $str = preg_replace('/x00|<[^>]*>?/', '', $string);
	   return str_replace(["'", '"'], ['&#39;', '&#34;'], $str);
	}}}
}

?>
