<?php
/**
 * ImgCaptions Plugin, HTML API
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

use Grav\Common\Page\Page;
use Grav\Common\Twig\Twig;
use Grav\Plugin\ImgCaptionsPlugin\API\Regex;
use TRegx\CleanRegex\Match\Details\Match;

/**
 * HTML API
 *
 * HTML API for rendering
 *
 * @category Extensions
 * @package  Grav\Plugin\ImgCaptionsPlugin\API
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/OleVik/grav-plugin-imgcaptions
 */
class HTML
{
    /**
     * Instantiate HTML API
     *
     * @param Twig   $twig   Twig-instance
     * @param Source $source Source-instance
     */
    public function __construct(Twig $twig, Source $source)
    {
        $this->twig = $twig;
        $this->source = $source;
    }

    /**
     * Parse tag for attributes
     *
     * @param string $tag HTML-tag
     *
     * @return array Associative array of attributes with values
     */
    protected static function attributes(string $tag)
    {
        return pattern(Regex::HTMLAttributes())
            ->match($tag)
            ->flatMap(function (Match $match) {
                return [
                    $match->group(1)->text() => $match->group(2)->text()
                ];
            });
    }

    /**
     * Build figure-tags
     *
     * @param string $content Page content
     *
     * @return string Processed content
     */
    public function render(string $content)
    {
        $content = pattern(Regex::HTMLParagraphWrapper())->replace($content)->all()->withReferences("$1");
        $matches = pattern(Regex::HTMLImage())->match($content)->all();
        foreach ($matches as $match) {
            $attrs = self::attributes($match);
            if (!isset($attrs['src']) && empty($attrs['src'])) {
                continue;
            }
            $source = $this->source->render($attrs['src'], GRAV_ROOT);
            $replace = $this->twig->processTemplate(
                'partials/figure.html.twig',
                [
                    'attrs' => $attrs,
                    'filename' => $source['filename'],
                    'page' => $source['page'] ?? null
                ]
            );
            $content = str_replace($match, $replace, $content);
        }
        return $content;
    }
}
