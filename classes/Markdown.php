<?php
/**
 * ImgCaptions Plugin, Markdown API
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

use Grav\Common\Utils;
use Grav\Common\Page\Page;
use Grav\Common\Twig\Twig;
use Grav\Plugin\ImgCaptionsPlugin\API\Source;
use Grav\Plugin\ImgCaptionsPlugin\API\Regex;

/**
 * Markdown API
 *
 * Markdown API for rendering
 *
 * @category Extensions
 * @package  Grav\Plugin\ImgCaptionsPlugin\API
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/OleVik/grav-plugin-imgcaptions
 */
class Markdown
{
    /**
     * Instantiate Markdown API
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
     * Parse query for attributes
     *
     * @param string $query URL query parameter
     *
     * @return array Associative array of keys with values
     */
    protected static function query(string $query)
    {
        preg_match_all(
            Regex::markdownType(),
            $query,
            $attributes,
            PREG_SET_ORDER
        );
        $assoc = array();
        $validAttributes = array('class', 'id');
        foreach ($attributes as $attribute) {
            if ($attribute[1]== 'classes') {
                $attribute[1] = 'class';
            }

            if (in_array($attribute[1], $validAttributes)) {
                $assoc[$attribute[1]] = $attribute[2] ?? '';
            }
        }
        return $assoc;
    }

    /**
     * Parse extra for attributes
     *
     * @param string $query URL query parameter
     *
     * @return array Associative array of keys with values
     */
    protected static function extra(string $query)
    {
        $extras = explode(' ', $query);
        $assoc = $id = $classes = $attributes = array();
        foreach ($extras as $extra) {
            if (Utils::startsWith($extra, '#')) {
                $id[] = substr($extra, 1);
            } elseif (Utils::startsWith($extra, '.')) {
                $classes[] = substr($extra, 1);
            } else {
                $attributes[] = $extra;
            }
        }
        if (!empty($id)) {
            $assoc['id'] = implode(' ', $id);
        }
        if (!empty($classes)) {
            $assoc['class'] = implode(' ', $classes);
        }
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                if (Utils::contains($attribute, '=')) {
                    $attribute = explode('=', $attribute);
                    $assoc[$attribute[0]] = $attribute[1];
                }
            }
        }
        return $assoc;
    }

    /**
     * Unwrap images from link
     *
     * @param string $content Page content
     *
     * @return string Process content
     */
    public static function unwrap(string $content)
    {
        preg_match_all(
            Regex::markdownAnchorWrapper(),
            $content,
            $wrappers,
            PREG_SET_ORDER
        );
        if (!empty($wrappers)) {
            foreach ($wrappers as $wrap) {
                $content = str_replace($wrap[0], $wrap['image'] . '___' . $wrap['url'], $content);
            }
        }
        return $content;
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
        $content = self::unwrap($content);
        preg_match_all(
            Regex::markdownImage(),
            $content,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $match) {
            $attrs = array();
            $attrs['src'] = $match['file'] . '.' . $match['ext'];
            if (isset($match['mediaActions'])) {
                $Source = $this->Source->render(
                    $attrs['src'],
                    '',
                    $match['mediaActions']
                );
            } else {
                $Source = $this->Source->render(
                    $attrs['src'],
                    ''
                );
            }
            $attrs['src'] = $Source['src'];
            if (isset($Source['filename']) && !empty($Source['filename'])) {
                $filename = $Source['filename'];
            }
            if (isset($Source['page']) && $Source['page'] instanceof Page) {
                $page = $Source['page'];
            }
            $attrs['alt'] = (isset($match['alt']) ? $match['alt'] : '');
            $attrs['title'] = (isset($match['title']) ? $match['title'] : '');
            if (isset($match['mediaActions']) && !empty($match['mediaActions'])) {
                $query = self::query(trim($match['mediaActions']), '? ');
                if (!empty($query)) {
                    foreach ($query as $key => $value) {
                        $attrs[$key] = $value;
                    }
                }
            }
            if (isset($match['extra']) && !empty($match['extra'])) {
                $extra = self::extra($match['extra']);
                if (!empty($extra)) {
                    foreach ($extra as $key => $value) {
                        $attrs[$key] = $value;
                    }
                }
            }
            if (isset($match['url']) && !empty($match['url'])) {
                $content = str_replace($match['url'], '', $content);
                $match[0] = str_replace($match['url'], '', $match[0]);
                $url = trim($match['url'], '_');
            }
            $replace = $this->Twig->processTemplate(
                'partials/figure.html.twig',
                [
                    'attrs' => $attrs,
                    'filename' => $filename ?? null,
                    'url' => $url ?? null,
                    'page' => $page ?? null
                ]
            );
            $content = str_replace($match[0], $replace, $content);
        }
        return $content;
    }
}
