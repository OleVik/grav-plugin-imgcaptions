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
    public function __construct(Page $Page, Pages $Pages)
    {
        $this->Page = $Page;
        $this->Pages = $Pages;
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
        $Page = $Media = $src = null;
        if (Utils::contains($source, '/')) {
            if (Utils::startsWith($source, '..')) {
                chdir($this->Page->path());
                $folder = str_replace('\\', '/', realpath($source));
                $Page = $this->Pages->get(dirname($folder));
            } elseif (Utils::startsWith($source, '/')) {
                $Page = $this->Pages->find($prefix . dirname($source));
            } else {
                $Page = $this->Pages->find('/' . dirname($source));
            }
        } else {
            $Page = $this->Page;
        }
        if ($Page !== null) {
            $Media = new Media($Page->path());
            if ($Media->get(basename($source))) {
                /* WIP: Return string, not Link-object */
                /* $Medium = $Media->get(basename($source));
                if ($mediaActions != null) {
                    $Medium = Excerpts::processMediaActions(
                        $Medium,
                        $mediaActions,
                        $Page
                    );
                }
                $src = $Medium->url();
                dump($Medium);
                exit(); */
                $src = $Media->get(basename($source))->url();
            } else {
                $src = $source;
            }
        }
        return [
            'src' => $src,
            'filename' => basename($source) ?? null,
            'page' => $Page
        ];
    }
}
