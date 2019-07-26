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
     *
     * @return array Image source, filename, and optionally Page
     */
    public function render(string $source, string $prefix = '')
    {
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            return [
                'src' => $source,
                'filename' => basename($source) ?? null
            ];
        }
        if (Utils::contains($source, '/')) {
            if ($prefix !== '') {
                $page = $this->pages->get($prefix . dirname($source));
            } else {
                $page = $this->pages->find(dirname($source));
            }
            return [
                'src' => $page->url() . DS . basename($source),
                'filename' => basename($source) ?? null,
                'page' => $page
            ];
        } else {
            return [
                'src' => $this->page->url() . DS . $source,
                'filename' => $source ?? null,
                'page' => $this->page
            ];
        }
    }
}
