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
 * PatternBuilder
 *
 * A fluent api that simplifies writing regular expressions.
 *
 * @category Extensions
 * @package  RegexBuilder
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/iaK/regexbuilder/blob/master/src/PatternBuilder.php Original by Isak Berglind (MIT)
 */
class PatternBuilder
{
    private $pattern = [];
    private $metaCharacters = '^[].${}*(\+)|/?<>';
    private $groupCharacters = "]";

    /**
     * Alias of pattern
     *
     * @param string $pattern Regular Expression
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function raw($pattern)
    {
        return $this->pattern($pattern);
    }

    /**
     * Adds a raw pattern to the pattern
     *
     * @param string $pattern Regular Expression
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function pattern($pattern)
    {
        $this->pattern[] = $pattern;
        return $this;
    }

    /**
     * Words or symbols to match
     *
     * @param string $symbols Symbols to match
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function symbols($symbols)
    {
        $this->pattern[] = $this->escape($symbols);
        return $this;
    }

    /**
     * Alias of symbols method
     *
     * @param string $symbol Symbols to match
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function symbol($symbol)
    {
        return $this->symbols($symbol);
    }

    /**
     * Matches a digit character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function digit()
    {
        $this->pattern[] = "\d";
        return $this;
    }

    /**
     * Matches a non-digit characters
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function notDigit()
    {
        $this->pattern[] = "\D";
        return $this;
    }

    /**
     * Matches a whitespace character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function whitespace()
    {
        $this->pattern[] = "\s";
        return $this;
    }

    /**
     * Matches a non whitespace character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function notWhitespace()
    {
        $this->pattern[] = "\S";
        return $this;
    }

    /**
     * Matches a word character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function char()
    {
        $this->pattern[] = "\w";
        return $this;
    }

    /**
     * Matches a non word character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function notChar()
    {
        $this->pattern[] = "\W";
        return $this;
    }

    /**
     * Matches a hex digit character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function hexDigit()
    {
        $this->pattern[] = "\x";
        return $this;
    }

    /**
     * Matches an octal digit character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function octalDigit()
    {
        $this->pattern[] = "\O";
        return $this;
    }

    /**
     * Matches a new line character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function newLine()
    {
        $this->pattern[] = "\n";
        return $this;
    }

    /**
     * Matches a carrage return character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function carriageReturn()
    {
        $this->pattern[] = "\r";
        return $this;
    }

    /**
     * Matches a tab character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function tab()
    {
        $this->pattern[] = "\t";
        return $this;
    }

    /**
     * Matches a vertical tab character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function verticalTab()
    {
        $this->pattern[] = "\v";
        return $this;
    }

    /**
     * Matches a form feed character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function formFeed()
    {
        $this->pattern[] = "\f";
        return $this;
    }

    /**
     * Matches a space character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function space()
    {
        $this->pattern[] = " ";
        return $this;
    }

    /**
     * Adds one or more of the previous group, character set or character
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function oneOrMore()
    {
        $this->pattern[] = "+";
        return $this;
    }

    /**
     * Adds zero or more of the previous group, character set or character
     *
     * @return [type] [description]
     */
    public function zeroOrOne()
    {
        $this->pattern[] = "?";
        return $this;
    }

    /**
     * Adds zero or more of the previous group, character set or character
     *
     * @return [type] [description]
     */
    public function zeroOrMore()
    {
        $this->pattern[] = "*";
        return $this;
    }

    /**
     * Matches any symbol
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function any()
    {
        $this->pattern[] = ".";
        return $this;
    }

    /**
     * Matches character sets or characters before this,
     * or after this
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function or()
    {
        $this->pattern[] = "|";
        return $this;
    }

    /**
     * Specifies the number of previous group, character set or character
     *
     * @param int $start Sets the minimum number of previous characters to match
     *                   if two parameters, or the precise number if one parameter
     * @param int $end (optional) Sets the maximum number of previous characters
     *                   to match
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function count()
    {
        $args = func_get_args();
        $this->pattern[] = "{" . implode(",", $args) . "}";
        return $this;
    }

    /**
     * Sets a character or digit range
     *
     * @param mixed $start Where to start
     * @param mixed $end Where to end
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function range($start, $end)
    {
        $this->pattern[] = $start . "-" . $end;
        return $this;
    }

    /**
     * Matches a word, either specified in $word or any word
     *
     * @param array|string $word (optional) Word to match
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function word($word = null)
    {
        if (gettype($word) == "array") {
            $this->pattern[] = "(" . implode("|", $word) . ")";
        } else {
            $this->pattern[] = $word ? $word : "\w+";
        }
        return $this;
    }

    /**
     * Matches a non word
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function notWord()
    {
        $this->pattern[] = "\W+";
        return $this;
    }

    /**
     * Matches a sequence of words
     *
     * @param mixed $words (optional) Words to match
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function words($words = null)
    {
        if (gettype($words) == "array") {
            $this->pattern[] = "(" . implode("|", $words) . ")";
        } else {
            $this->pattern[] = $words ?? "[\s\w]+?";
        }
        return $this;
    }

    /**
     * Matches a character group
     *
     * @param mixed $args Callback or pattern
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function group($args)
    {
        $pattern = $this->callbackOrPattern($args);
        $pattern = $this->escapeGroup($pattern);
        $this->pattern[] = "[$pattern]";
        return $this;
    }

    /**
     * Matches a character group, negatively
     *
     * @param mixed $args Callback or pattern
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function negativeGroup($args)
    {
        $pattern = $this->callbackOrPattern($args);
        $pattern = $this->escapeGroup($pattern);
        $this->pattern[] = "[^$pattern]";
        return $this;
    }

    /**
     * Matches previous pattern in a group
     *
     * @param mixed $callback Callback or pattern
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function capture($callback = null)
    {
        if ($callback && gettype($callback) == "object") {
            $pattern = $callback(new static);
        } else {
            $pattern = array_pop($this->pattern);
        }
        $this->pattern[] = "(" . $pattern . ")";
        return $this;
    }

    /**
     * Matches previous pattern in a group, negatively
     *
     * @param mixed $callback Callback or pattern
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function optionalCapture($callback = null)
    {
        if ($callback && gettype($callback) == "object") {
            $pattern = $callback(new static);
        } else {
            $pattern = array_pop($this->pattern);
        }
        $this->pattern[] = "(?:" . $pattern . ")";
        return $this;
    }

    /**
     * Matches previous pattern in a group,
     * with name set
     *
     * @param string $name Group name
     * @param mixed $callback Callback or pattern
     *
     * @return string
     */
    public function namedCapture(string $name, $callback = null)
    {
        if ($callback && gettype($callback) == "object") {
            $pattern = $callback(new static);
        } else {
            $pattern = array_pop($this->pattern);
        }
        $this->pattern[] = "(?'$name'" . $pattern . ")";
        return $this;
    }

    /**
     * Starts a capture group
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function startCapture()
    {
        $this->pattern[] = "(";
        return $this;
    }

    /**
     * Ends a capture group
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function endCapture()
    {
        $this->pattern[] = ")";
        return $this;
    }

    /**
     * Sets a look behind pattern
     *
     * @param mixed $arg Callback or pattern
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function behind($arg)
    {
        $pattern = $this->callbackOrPattern($arg);
        $this->pattern[] = "(?<=$pattern)";
        return $this;
    }

    /**
     * Alias of behind()
     *
     * @param mixed $arg Callback or pattern
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function beginsWith($arg)
    {
        return $this->behind($arg);
    }

    /**
     * Alias of behind()
     *
     * @param mixed $arg Callback or pattern
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function before($arg)
    {
        return $this->behind($arg);
    }

    /**
     * Sets a positive look ahead pattern
     *
     * @param mixed $arg Callback or pattern
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function after($arg)
    {
        $pattern = $this->callbackOrPattern($arg);
        $this->pattern[] = "(?=$pattern)";
        return $this;
    }

    /**
     * Sets a negative look ahead pattern
     *
     * @param mixed $arg Callback or pattern
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function negativeAfter($arg)
    {
        $pattern = $this->callbackOrPattern($arg);
        $this->pattern[] = "(?!$pattern)";
        return $this;
    }

    /**
     * Alias of after()
     *
     * @param mixed $arg Callback or pattern
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function endsWith($arg)
    {
        return $this->after($arg);
    }

    /**
     * Makes the previous group, character set or character optional
     *
     * @param mixed $start (optional) Start of replace if two params or
     *                     the string to make optional if one param
     * @param int $end (optional) End of replace
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function optional()
    {
        $args = func_get_args();
        $pattern = array_pop($this->pattern);

        if (count($args) == 2) {
            $pattern = $this->optionalSubstring($pattern, $args[0], $args[1]);
        } elseif (count($args) == 1) {
            $pattern = $this->optionalSymbols($pattern, $args[0]);
        } else {
            $pattern .= "?";
        }
        $this->pattern[] = $pattern;
        return $this;
    }

    /**
     * Makes a part of the pattern optional using a string
     *
     * @param string $pattern Pattern
     * @param string $optional What to make optional
     *
     * @return string New pattern
     */
    public function optionalSymbols($pattern, $optional)
    {
        return str_replace(
            $optional,
            $this->optionalCaptionGroup($optional),
            $pattern
        );
    }

    /**
     * Makes part of the pattern using substr
     *
     * @param string $pattern Pattern
     * @param int $start Where to start
     * @param int $end Where to end
     *
     * @return string New pattern
     */
    public function optionalSubstring($pattern, $start, $end)
    {
        $subject = substr($pattern, $start, $end);
        $optional = $this->optionalCaptionGroup($subject);
        return substr($pattern, 0, $start)
            . $optional
            . substr($pattern, $end + $start, strlen($pattern) - 1);
    }

    /**
     * Escapes a pattern
     *
     * @param string $pattern Pattern to escape
     *
     * @return string Escaped pattern
     */
    public function escape($pattern)
    {
        $pattern = array_map(
            function ($char) {
                return in_array($char, str_split($this->metaCharacters))
                    ? "\\$char"
                    : $char;
            },
            str_split($pattern)
        );

        return implode($pattern);
    }

    /**
     * Escapes a group
     *
     * @param string $pattern Group of characters to escape
     *
     * @return string Escaped characters
     */
    protected function escapeGroup($pattern)
    {
        $pattern = array_map(
            function ($char) {
                return in_array($char, str_split($this->groupCharacters))
                    ? "\\$char"
                    : $char;
            },
            str_split($pattern)
        );
        return implode($pattern);
    }

    /**
     * Makes a pattern an optional caption group
     *
     * @param mixed $args Callback or pattern
     *
     * @return string Pattern wrapped in a optional caption group
     */
    protected function optionalCaptionGroup($args)
    {
        $pattern = $this->callbackOrPattern($args);
        return "(?:" . $pattern . ")?";
    }

    /**
     * Matches the built up pattern and return only the match
     *
     * @param string $string Subject
     *
     * @return array Match
     */
    public function match($string)
    {
        $match = $this->matchWithGroups($string);

        return empty($match) ? false : $match[0];
    }

    /**
     * Matches all of the build up pattern and returns the full output
     *
     * @param string $string Subject
     *
     * @return array Match
     */
    public function matchWithGroups($string)
    {
        preg_match($this->getPattern(), $string, $output);

        return $output;
    }

    /**
     * Matches all of the built up pattern and returns only the match
     *
     * @param string $string Subject
     *
     * @return mixed Match
     */
    public function matchAll($string)
    {
        $match = $this->matchAllWithGroups($string);

        return empty($match) ? false : $match[0];
    }

    /**
     * Matches all of the build up pattern and returns the full output
     *
     * @param string $string Subject
     *
     * @return array Match
     */
    public function matchAllWithGroups($string)
    {
        preg_match_all($this->getPattern(), $string, $output);

        return $output;
    }

    /**
     * Replaces the matches
     *
     * @param string $string What to replace with
     * @param string $subject What to replace in
     *
     * @return string Replaced string
     */
    public function replace($string, $subject)
    {
        return pattern($this->getPattern())->replace($subject)->all()->with($string);
    }

    /**
     * Returns the built up pattern
     *
     * @param string $flags (optional) Regular expression flags
     *
     * @return string Pattern
     */
    public function getPattern($flags = '')
    {
        return "/" . implode($this->pattern) . "/" . $flags;
    }

    /**
     * Calls the callback if param is a callback, or return the pattern if a string
     * is provided
     *
     * @param mixed $args Callback or pattern
     *
     * @return mixed
     */
    protected function callbackOrPattern($args)
    {
        return gettype($args) == "object"
            ? $args(new static)
            : $args;
    }

    /**
     * Removes everything from pattern
     *
     * @return RegexBuilder\PatternBuilder
     */
    public function release()
    {
        $this->pattern = [];
        return $this;
    }

    /**
     * Convert the pattern to a string
     *
     * @return string Pattern
     */
    public function __toString()
    {
        return implode($this->pattern);
    }
}
