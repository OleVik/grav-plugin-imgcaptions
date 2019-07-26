<?php
/**
 * ImgCaptions Plugin, Regex API
 *
 * PHP version 7
 *
 * @category   API
 * @package    Grav\Plugin\ImgCaptionsPlugin
 * @subpackage Grav\Plugin\ImgCaptionsPlugin\API
 * @author     Ole Vik <git@olevik.net>
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @link       https://github.com/OleVik/grav-plugin-imgcaptions
 */
namespace Grav\Plugin\ImgCaptionsPlugin\API;

use RegexBuilder\Regex as RegexBuilder;

/**
 * Regex API
 *
 * Static methods for building regular expressions
 *
 * @category Extensions
 * @package  Grav\Plugin\ImgCaptionsPlugin\API
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/OleVik/grav-plugin-imgcaptions
 */
class Regex
{
    /**
     * Get all regular expressions
     *
     * @return array
     */
    public function all()
    {
        $class = new \ReflectionClass(__CLASS__);
        $methods = $class->getMethods(\ReflectionMethod::IS_STATIC);
        $output = array();
        foreach ($methods as $method) {
            if ($method->modifiers = 'public static'
                && !empty($method->getParameters())
                && $method->getParameters()[0]->name == 'flags'
            ) {
                $name = $method->name;
                $output[$name] = Regex::$name();
            }
        }
        return $output;
    }

    /**
     * Paragraph wrapper around Image regular expression, HTML
     *
     * @param string $flags Regular expression flags/modifiers
     *
     * @return string
     */
    public static function HTMLParagraphWrapper(string $flags = 'miu')
    {
        return RegexBuilder::symbols('<p>')
            ->whitespace()
            ->zeroOrMore()
            ->capture(
                function ($builder) {
                    return $builder
                        ->symbols('<a')
                        ->any()
                        ->zeroOrMore()
                        ->symbols('<img')
                        ->any()
                        ->zeroOrMore()
                        ->symbols('</a>')
                        ->or()
                        ->symbols('<img')
                        ->any()
                        ->zeroOrMore();
                }
            )
            ->optional()
            ->whitespace()
            ->zeroOrMore()
            ->symbols('</p>')
            ->getPattern($flags);
    }

    /**
     * Image regular expression, HTML
     *
     * @param string $flags Regular expression flags/modifiers
     *
     * @return string
     */
    public static function HTMLImage(string $flags = 'miu')
    {
        return RegexBuilder::symbols('<img')
            ->any()
            ->zeroOrMore()
            ->symbols('/')
            ->optional()
            ->symbols('>')
            ->getPattern($flags);
    }

    /**
     * Attributes and values regular expression, HTML
     *
     * @param string $flags Regular expression flags/modifiers
     *
     * @return string
     */
    public static function HTMLAttributes(string $flags = 'miu')
    {
        return
            RegexBuilder::capture(
                function ($builder) {
                    return $builder->notWhitespace()
                        ->oneOrMore();
                }
            )
            ->whitespace()
            ->zeroOrMore()
            ->symbols('=')
            ->whitespace()
            ->zeroOrMore()
            ->group('\'"')
            ->optional()
            ->capture(
                function ($builder) {
                    return $builder
                        ->optionalCapture(
                            function ($builder) {
                                return $builder
                                    ->any()
                                    ->negativeAfter(
                                        function ($builder) {
                                            return $builder
                                                ->group('\'"')
                                                ->optional()
                                                ->whitespace()
                                                ->oneOrMore()
                                                ->optionalCapture(
                                                    function ($builder) {
                                                        return $builder
                                                            ->notWhitespace()
                                                            ->oneOrMore();
                                                    }
                                                )
                                                ->symbols('=')
                                                ->or()
                                                ->group('>\'"');
                                        }
                                    );
                            }
                        )
                        ->optional()
                        ->negativeGroup('\'"')
                        ->zeroOrMore();
                }
            )
            ->group('\'"')
            ->optional()
            ->getPattern($flags);
    }

    /**
     * Anchor-wrapper regular expression, Markdown
     *
     * @param string $flags Regular expression flags/modifiers
     *
     * @return string
     */
    public static function markdownAnchorWrapper(string $flags = 'miu')
    {
        return RegexBuilder::symbols('[')
            ->namedCapture(
                'image',
                function ($builder) {
                    return $builder
                        ->symbols('!')
                        ->any()
                        ->zeroOrMore();
                }
            )
            ->symbols('](')
            ->namedCapture(
                'url',
                function ($builder) {
                    return $builder
                        ->word(['http', 'https', 'ftp', 'file'])
                        ->symbols("://")
                        ->capture(
                            function ($query) {
                                return $query->symbols("www.");
                            }
                        )
                        ->optional()
                        ->any()
                        ->zeroOrMore();
                }
            )
            ->symbols(')')
            ->getPattern($flags);
    }

    /**
     * Image regular expression, Markdown
     *
     * @param string $flags Regular expression flags/modifiers
     *
     * @return string
     */
    public static function markdownImage(string $flags = 'miu')
    {
        return RegexBuilder::symbols('!')
            ->pattern(self::markdownAlt())
            ->whitespace()
            ->zeroOrMore()
            ->pattern(self::markdownFile())
            ->whitespace()
            ->zeroOrMore()
            ->pattern(self::markdownExtra())
            ->pattern(self::markdownAnchorAppend())
            ->getPattern($flags);
    }


    /**
     * Alt-name regular expression, Markdown
     *
     * @return string
     */
    public static function markdownAlt()
    {
        return RegexBuilder::symbols('[')
            ->namedCapture(
                'alt',
                function ($builder) {
                    return $builder
                        ->any()
                        ->zeroOrMore();
                }
            )
            ->symbols(']')
            ->__toString();
    }

    /**
     * File regular expression, Markdown
     *
     * @return string
     */
    public static function markdownFile()
    {
        return RegexBuilder::symbols('(')
            ->namedCapture(
                'file',
                function ($builder) {
                    return $builder
                        ->any()
                        ->zeroOrMore();
                }
            )
            ->pattern(self::imageExtension())
            ->pattern(self::grav())
            ->whitespace()
            ->zeroOrMore()
            ->pattern(self::markdownTitle())
            ->symbols(')')
            ->__toString();
    }

    /**
     * Extension regular expression
     *
     * @return string
     */
    public static function imageExtension()
    {
        return RegexBuilder::symbols('.')
            ->namedCapture(
                'ext',
                function ($builder) {
                    return $builder
                        ->word(['png', 'gif', 'jpg', 'jpeg']);
                }
            )
            ->__toString();
    }

    /**
     * Grav-syntax regular expression
     *
     * @return string
     */
    public static function grav()
    {
        return RegexBuilder::namedCapture(
            'grav',
            function ($builder) {
                return $builder
                    ->symbols('?')
                    ->oneOrMore()
                    ->any()
                    ->zeroOrMore()
                    ->negativeGroup(
                        function ($builder) {
                            return $builder
                                ->whitespace()
                                ->symbol('"');
                        }
                    );
            }
        )
        ->optional()
        ->__toString();
    }

    /**
     * Type regular expression, Markdown
     *
     * @param string $flags Regular expression flags/modifiers
     *
     * @return string
     */
    public static function markdownType(string $flags = 'miu')
    {
        return RegexBuilder::word()
            ->capture()
            ->optionalCapture(
                function ($builder) {
                    return $builder
                        ->symbols('=')
                        ->word()
                        ->capture();
                }
            )
            ->zeroOrOne()
            ->getPattern($flags);
    }

    /**
     * Title regular expression, Markdown
     *
     * @return string
     */
    public static function markdownTitle()
    {
        return RegexBuilder::startCapture()
            ->symbols('"')
            ->namedCapture(
                'title',
                function ($builder) {
                    return $builder
                        ->any()
                        ->zeroOrMore();
                }
            )
            ->optional()
            ->symbols('"')
            ->endCapture()
            ->optional()
            ->__toString();
    }

    /**
     * Extra-syntax regular expression, Markdown
     *
     * @return string
     */
    public static function markdownExtra()
    {
        return RegexBuilder::startCapture()
            ->symbols('{')
            ->namedCapture(
                'extra',
                function ($builder) {
                    return $builder
                        ->any()
                        ->zeroOrMore();
                }
            )
            ->optional()
            ->symbols('}')
            ->endCapture()
            ->optional()
            ->__toString();
    }

    /**
     * Appended link regular expression, Markdown
     *
     * @return string
     */
    public static function markdownAnchorAppend()
    {
        return RegexBuilder::namedCapture(
            'url',
            function ($builder) {
                return $builder
                    ->symbols('___')
                    ->word(['http', 'https', 'ftp', 'file'])
                    ->symbols("://")
                    ->capture(
                        function ($query) {
                            return $query->symbols("www.");
                        }
                    )
                    ->optional()
                    ->any()
                    ->zeroOrMore();
            }
        )
            ->optional()
            ->__toString();
    }
}
