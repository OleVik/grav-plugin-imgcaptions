<?php
/**
 * ImgCaptions Plugin
 *
 * PHP version 7
 *
 * @category   Extensions
 * @package    Grav
 * @subpackage Scholar
 * @author     Ole Vik <git@olevik.net>
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @link       https://github.com/OleVik/grav-plugin-imgcaptions
 */
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Utils;
use Grav\Common\Page\Page;
use Grav\Common\Page\Pages;
use Grav\Common\Twig\Twig;
use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\ImgCaptionsPlugin\API\HTML;
use Grav\Plugin\ImgCaptionsPlugin\API\Markdown;
use Grav\Plugin\ImgCaptionsPlugin\API\Source;
use Grav\Plugin\ImgCaptionsPlugin\API\Regex;

/**
 * Turns the title-attribute in img-elements into figure-elements with a figcaption
 *
 * Class ImgCaptionsPlugin
 *
 * @category Extensions
 * @package  Grav\Plugin
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/OleVik/grav-plugin-imgcaptions
 */
class ImgCaptionsPlugin extends Plugin
{
    /**
     * Protected variables
     *
     * @var string $mode Grav cache setting
     */
    protected $mode;

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
        if ($this->isAdmin() || $this->config->get('plugins.imgcaptions.enabled') !== true) {
            return;
        }
        if ($this->config->get('system.pages.type') == "flex") {
            return;
        }
        
        $config = (array) $this->config->get('plugins.imgcaptions');
        $this->mode = $config['mode'];
        if (!isset($config['event'])) {
            if ($this->mode == 'markdown') {
                $event = 'onPageContentRaw';
            } elseif ($this->mode == 'html') {
                $event = 'onPageContentProcessed';
            }
        } else {
            $event = $config['event'];
        }
        $this->enable(
            [
                $event => ['output', 0],
                'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0]
            ]
        );
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
        $Page = $event['page'];
        $config = (array) $this->config->get('plugins.imgcaptions');
        $header = (array) $Page->header();
        if (isset($header['imgcaptions'])) {
            $config = Utils::arrayMergeRecursiveUnique(
                $config,
                $header['imgcaptions']
            );
        }
        if ($config['enabled'] !== true) {
            return;
        }
        include __DIR__ . '/vendor/autoload.php';
        $Source = new Source($Page, $this->grav['pages']);
        $content = $Page->getRawContent();
        if ($this->mode == 'markdown') {
            $Markdown = new Markdown($this->grav['twig'], $Source);
            $content = $Markdown->render($content);
        } elseif ($this->mode == 'html') {
            $HTML = new HTML($this->grav['twig'], $Source);
            $content = $HTML->render($content);
        }
        $Page->setRawContent($content);
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
}
