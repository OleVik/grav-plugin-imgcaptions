<?php
namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Uri;
use Grav\Common\Page\Page;
use Grav\Common\Twig\Twig;
use RocketTheme\Toolbox\Event\Event;

/**
 * Turns the title-attribute in img-elements into figure-elements with a figcaption
 *
 * Class ImgCaptionsPlugin
 * 
 * @package Grav\Plugin
 * @return  void
 * @license MIT License by Ole Vik
 */
class ImgCaptionsPlugin extends Plugin
{
    const REGEX_MARKDOWN_LINK = '/!\[(?\'alt\'.*)\]\s?\((?\'file\'.*)(?\'ext\'.png|.gif|.jpg|.jpeg)(?\'grav\'\??(?\'type\'\?id|classes|.*)\=*.*[^"])?\s*(?:\"(?\'title\'.*)\")*\)(?\'extra\'\{.*\})?(?\'url\'___https?:\/\/.*)?/';
    const REGEX_IMG = "/(<img(?:(\s*(class)\s*=\s*\x22([^\x22]+)\x22*)+|[^>]+?)*>)/";
    const REGEX_IMG_P = "/<p>\s*?(<a .*<img.*<\/a>|<img.*)?\s*<\/p>/";
    const REGEX_IMG_TITLE = "/<img[^>]*?title[ ]*=[ ]*[\"](.*?)[\"][^>]*?>/";
    const REGEX_IMG_WRAPPING_LINK = '/\[(?\'image\'\!.*)\]\((?\'url\'https?:\/\/.*)\)/';

    /**
     * Register events with Grav
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     * 
     * @return array
     */
    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            return;
        }
        
        $config = (array) $this->config->get('plugins');
        $config = $config['imgcaptions'];
        if (!isset($config['event'])) {
            if ($config['mode'] == 'markdown') {
                $event = 'onPageContentRaw';
            } elseif ($config['mode'] == 'html') {
                $event = 'onPageContentProcessed';
            }
        } else {
            $event = $config['event'];
        }
        if ($config['enabled']) {
            $this->enable(
                [
                    $event => ['output', 0],
                    'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0]
                ]
            );
        } else {
            return;
        }
    }

    /**
     * Finds images in page content and rewraps as figures with figcaptions
     *
     * @param Event $event Instance of RocketTheme\Toolbox\Event\Event
     * 
     * @return void
     */
    public function output(Event $event)
    {
        $page = $event['page'];
        $uri = $this->grav['uri'];
        $uri = $uri->base().$page->rawRoute();
        $twig = $this->grav['twig'];
        $config = (array) $this->config->get('plugins');
        $config = $config['imgcaptions'];
        $content = $page->getRawContent();
        if ($config['mode'] == 'markdown') {
            preg_match_all(
                $this::REGEX_IMG_WRAPPING_LINK,
                $content,
                $wrappers,
                PREG_SET_ORDER
            );
            if (count($wrappers) != 0) {
                foreach ($wrappers as $wrap) {
                    $content = str_replace($wrap[0], $wrap['image'] . '___' . $wrap['url'], $content);
                }
            }
            preg_match_all(
                $this::REGEX_MARKDOWN_LINK,
                $content,
                $matches,
                PREG_SET_ORDER
            );
            foreach ($matches as $match) {
                $attrs = array();
                $attrs['src'] = $uri.DS.$match['file'].$match['ext'];
                $attrs['alt'] = (isset($match['alt']) ? $match['alt'] : '');
                $attrs['title'] = (isset($match['title']) ? $match['title'] : '');
                if (isset($match['type'])) {
                    if ($match['type'] == 'id') {
                        $id = substr($match['grav'], strpos($match['grav'], "=") + 1);
                        $attrs['id'] = $id;
                    } elseif ($match['type'] == 'classes') {
                        $classes = substr($match['grav'], strpos($match['grav'], "=") + 1);
                        $attrs['class'] = str_replace(',', ' ', $classes);
                    }
                }
                if (isset($match['extra']) && !empty($match['extra'])) {
                    $extra = trim($match['extra'], '{}');
                    $extras = explode(' ', $extra);
                    $id = $classes = $attributes = array();
                    foreach ($extras as $extra) {
                        if (self::_startsWith($extra, '#')) {
                            $id[] = substr($extra, 1);
                        } elseif (self::_startsWith($extra, '.')) {
                            $classes[] = substr($extra, 1);
                        } else {
                            $attributes[] = $extra;
                        }
                    }
                    if (!empty($id)) {
                        $attrs['id'] = implode(' ', $id);
                        if (!isset($match['type']) && $match['type'] == 'id') {
                            $attrs['id'] = $attrs['id'] . ' ' . implode(' ', $id);
                        } else {
                            $attrs['id'] = implode(' ', $id);
                        }
                    }
                    if (!empty($classes)) {
                        if (!isset($match['type']) && $match['type'] == 'classes') {
                            $attrs['class'] = $attrs['class'] . ' ' . implode(' ', $classes);
                        } else {
                            $attrs['class'] = implode(' ', $classes);
                        }
                    }
                    if (!empty($attributes)) {
                        foreach ($attributes as $attribute) {
                            if (self::_contains($attribute, '=')) {
                                $attribute = explode('=', $attribute);
                                $attrs[$attribute[0]] = $attribute[1];
                            }
                        }
                    }
                }
                if (isset($match['url']) && !empty($match['url'])) {
                    $replace = $twig->processTemplate(
                        'partials/figure.html.twig', 
                        [
                            'attrs' => $attrs,
                            'url' => trim($match['url'], '_')
                        ]
                    );
                } else {
                    $replace = $twig->processTemplate(
                        'partials/figure.html.twig', 
                        [
                            'attrs' => $attrs
                        ]
                    );
                }
                $content = str_replace($match[0], $replace, $content);
            }
        } elseif ($config['mode'] == 'html') {
            $content = $page->content();

            $unwrap = $this::REGEX_IMG_P;
            $content = preg_replace($unwrap, "$1", $content);

            $wrap = $this::REGEX_IMG;
            $content = preg_replace($wrap, '<figure role="group" $2>$1</figure>', $content);

            $title = $this::REGEX_IMG_TITLE;
            $content = preg_replace($title, "$0<figcaption>$1</figcaption>", $content);
        }
        $page->setRawContent($content);
    }

    /**
     * Add current directory to twig lookup paths.
     *
     * @return void
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Find first character in string.
     *
     * @param string $haystack Character
     * @param string $needle   String
     * 
     * @return boolean
     */
    private static function _startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * Search for string in string
     *
     * @param string $haystack String to search
     * @param string $needle   String to search for
     * 
     * @see https://stackoverflow.com/a/30128453/603387
     * 
     * @return void
     */
    private static function _contains($haystack, $needle)
    {
        if (mb_strpos($needle, $haystack) !== false) {
            return true;
        }
    }

    /**
     * Recursive array_map implementation
     *
     * @param callable $func  Internal PHP-function
     * @param array    $array Array to map
     * 
     * @see http://php.net/manual/en/function.array-map.php#116938
     * 
     * @return array
     */
    private static function _arrayMapRecursive(callable $func, array $array)
    {
        return filter_var($array, \FILTER_CALLBACK, ['options' => $func]);
    }
}
