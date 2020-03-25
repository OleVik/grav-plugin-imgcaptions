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
     * @param Twig   $Twig   Twig-instance
     * @param Source $Source Source-instance
     */
    public function __construct(Twig $Twig, Source $Source)
    {
        $this->Twig = $Twig;
        $this->Source = $Source;
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
        preg_match_all(
            Regex::HTMLAttributes(),
            $tag,
            $attributes,
            PREG_SET_ORDER
        );
        $assoc = array();
        foreach ($attributes as $attribute) {
            $assoc[$attribute[1]] = $attribute[2];
        }
        return $assoc;
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
        $content = preg_replace(Regex::HTMLParagraphWrapper(), "$1", $content);
        preg_match_all(
            Regex::HTMLImage(),
            $content,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $match) {
            $attrs = self::attributes($match[0]);
            if (!isset($attrs['src']) && empty($attrs['src'])) {
                continue;
            }
            $Source = $this->Source->render($attrs['src'], GRAV_ROOT);
            $replace = $this->Twig->processTemplate(
                'partials/figure.html.twig',
                [
                    'attrs' => $attrs,
                    'filename' => $Source['filename'],
                    'page' => $Source['page'] ?? null
                ]
            );
            $content = str_replace($match[0], $replace, $content);
        }
        return $content;
    }
}
