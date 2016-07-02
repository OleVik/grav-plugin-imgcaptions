<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

class ImgCaptionsPlugin extends Plugin
{
    public static function getSubscribedEvents() {
        return [
            'onPageContentProcessed' => ['onPageContentProcessed', 0]
        ];
    }

    public function onPageContentProcessed(Event $event)
    {
        $page = $event['page'];
        $pluginsobject = (array) $this->config->get('plugins');
        $pageobject = $this->grav['page'];
		if (isset($pluginsobject['imgcaptions'])) {
            if ($pluginsobject['imgcaptions']['enabled']) {
				$buffer = $page->content();
				$url = $page->url();
				/* Unwrap <img> from <p> */
				$buffer = preg_replace("/<p>\s*?(<a .*<img.*<\/a>|<img.*)?\s*<\/p>/",
					"$1",
					$buffer);
				/* Wrap <img> in <figure>, include class-attribute if set. */
				$buffer = preg_replace("/(<img(?:(\s*(class)\s*=\s*\x22([^\x22]+)\x22*)+|[^>]+?)*>)/",
                    '<figure$2>$1</figure>',
                    $buffer);
				/* If img-elements have a title set by Markdown, 
				*  append them as <figcaption> them within <figure>. */
				$buffer = preg_replace("/<img[^>]*?title=\x22([^\x22]*)\x22[^>]*?src=\x22([^\x22]*)[^>]*?>|<img[^>]*?src=\x22([^\x22]*)\x22[^>]*?title=\x22([^\x22]*)\x22[^>]*?>/",
					"$0<figcaption>$1</figcaption>",
					$buffer);
				$page->setRawContent($buffer);
            }
        }
    }
}