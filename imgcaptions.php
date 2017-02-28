<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Turns the title-attribute in img-elements into figure-elements with a figcaption
 *
 * Class ImgCaptionsPlugin
 * @package Grav\Plugin
 * @return void
 * @license MIT License by Ole Vik
 */
class ImgCaptionsPlugin extends Plugin
{

    /**
     * Register events with Grav
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPageContentProcessed' => ['onPageContentProcessed', 0]
        ];
    }

    /**
     * Finds images in page content and rewraps as figures with figcaptions
     * @return void
     */
    public function onPageContentProcessed(Event $event)
    {
        /* Check if Admin-interface */
        if ($this->isAdmin()) {
            return;
        }

        $page = $event['page'];
        $pluginsobject = (array) $this->config->get('plugins');
        $pageobject = $this->grav['page'];
        if (isset($pluginsobject['imgcaptions'])) {
            if ($pluginsobject['imgcaptions']['enabled']) {
                $buffer = $page->content();
                $url = $page->url();

                /* Unwrap <img> from <p> */
                $unwrap = "/<p>\s*?(<a .*<img.*<\/a>|<img.*)?\s*<\/p>/";
                $buffer = preg_replace($unwrap, "$1", $buffer);

                /* Wrap <img> in <figure>, include class-attribute if set. */
                $wrap = "/(<img(?:(\s*(class)\s*=\s*\x22([^\x22]+)\x22*)+|[^>]+?)*>)/";
                $buffer = preg_replace($wrap, '<figure$2>$1</figure>', $buffer);

                /* If img-elements have a title set by Markdown, append them as <figcaption> them within <figure>. */
                $title = "/<img[^>]*?title=\x22([^\x22]*)\x22[^>]*?src=\x22([^\x22]*)[^>]*?>|<img[^>]*?src=\x22([^\x22]*)\x22[^>]*?title=\x22([^\x22]*)\x22[^>]*?>/";
                $buffer = preg_replace($title, "$0<figcaption>$1</figcaption>", $buffer);

                $page->setRawContent($buffer);
            }
        }
    }
}
