<?php
/**
 * ImgCaptions Plugin, RegexBuilder
 *
 * PHP version 7
 *
 * @category API
 * @package  RegexBuilder
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/iaK/regexbuilder Original by Isak Berglind (MIT)
 */
namespace RegexBuilder;

/**
 * RegexBuilder
 *
 * A fluent api that simplifies writing regular expressions.
 *
 * @category Extensions
 * @package  RegexBuilder
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/iaK/regexbuilder/blob/master/src/Regex.php Original by Isak Berglind (MIT)
 */
class Regex
{
    /**
     * Function caller
     *
     * @param string $method Method to call
     * @param mixed  $args   Parameters to pass
     *
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return (new PatternBuilder)->{$method}(...$args);
    }
}
