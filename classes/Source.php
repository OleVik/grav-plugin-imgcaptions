<?php
/**
 * ImgCaptions Plugin, Source API
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
use Grav\Common\Page\Pages;
use Grav\Common\Page\Media;
use Grav\Common\Helpers\Excerpts;

/**
 * Source API
 *
 * Source API for rendering
 *
 * @category Extensions
 * @package  Grav\Plugin\ImgCaptionsPlugin\API
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/OleVik/grav-plugin-imgcaptions
 */
class Source
{
    /**
     * Instantiate Markdown API
     *
     * @param Page  $page  Page-instance
     * @param Pages $pages Pages-instance
     */
    public function __construct(Page $page, Pages $pages)
    {
        $this->page = $page;
        $this->pages = $pages;
    }

    /**
     * Determine origin of image
     *
     * @param string $source Image src-attribute
     * @param string $prefix Optional prefix to Page location
     * @param string $mediaActions Optional media actions on the image (resize, etc)
     *
     * @return array Image source, filename, and optionally Page
     */
    public function render(string $source, string $prefix = '', string $mediaActions = null)
    {
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            return [
                'src' => $source,
                'filename' => basename($source) ?? null
            ];
        }
        $source = urldecode($source);
        $page = $media = $src = null;
        if (Utils::contains($source, '/')) {
            if (Utils::startsWith($source, '..')) {
                chdir($this->page->path());
                $folder = str_replace('\\', '/', realpath($source));
                $page = $this->pages->get(dirname($folder));
            } elseif (Utils::startsWith($source, '/')) {
                $page = $this->pages->find($prefix . dirname($source));
            } else {
                $page = $this->pages->find('/' . dirname($source));
            }
        } else {
            $page = $this->page;
        }
        if ($page !== null) {
            $media = new Media($page->path());
            if ($media->get(basename($source))) {
                $medium = $media->get(basename($source));
                if ($mediaActions != null) {
                    $medium = Excerpts::processMediaActions($medium, $mediaActions, $page);
                }
                $src = $medium->url();
            } else {
                $src = $source;
            }
        }
        return [
            'src' => $src,
            'filename' => basename($source) ?? null,
            'page' => $page
        ];
    }
}
