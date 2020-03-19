<?php
/**
 * ImgCaptions Plugin, Unit Tests
 *
 * PHP version 7
 *
 * @category API
 * @package  Grav\Plugin\ImgCaptionsPlugin
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/OleVik/grav-plugin-imgcaptions
 */
namespace Grav\Plugin\ImgCaptionsPlugin;

use Grav\Plugin\ImgCaptionsPlugin\API\Regex;

/**
 * Unit Tests
 *
 * @category Extensions
 * @package  Grav\Plugin\ImgCaptionsPlugin
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/OleVik/grav-plugin-imgcaptions
 */
class ImgCaptionsTest extends \Codeception\Test\Unit
{

    /**
     * Markdown sample image-links
     *
     * @var array
     */
    protected $testData;

    /**
     * Markdown sample image-links with titles
     *
     * @var array
     */
    protected $testDataTitles;

    /**
     * Instance of Parsedown, Markdown-parser
     *
     * @var Parsedown
     */
    protected $parsedown;

    /**
     * PCRE-pattern for parsing Markdown image-links
     *
     * @deprecated 3.0.0
     */
    const REGEX_MARKDOWN_LINK = '/!\[(?\'alt\'.*)\]\s?\((?\'file\'.*)(?\'ext\'.png|.gif|.jpg|.jpeg)(?\'mediaActions\'\??(?\'type\'\?id|classes|.*)\=*.*[^"])?\s*(?:\"(?\'title\'.*)\")*\)(?\'extra\'\{.*\})?(?\'url\'___https?:\/\/.*)?/';

    /**
     * PCRE-pattern for parsing HTML img-tags
     *
     * @deprecated 3.0.0
     */
    const REGEX_IMG = "/(<img(?:(\s*(class)\s*=\s*\x22([^\x22]+)\x22*)+|[^>]+?)*>)/";

    /**
     * PCRE-pattern for parsing HTML img-tags in p-tags
     *
     * @deprecated 3.0.0
     */
    const REGEX_IMG_P = "/<p>\s*?(<a .*<img.*<\/a>|<img.*)?\s*<\/p>/";

    /**
     * PCRE-pattern for parsing title-attribute in HTML img-tags
     *
     * @deprecated 3.0.0
     */
    const REGEX_IMG_TITLE = "/<img[^>]*?title[ ]*=[ ]*[\"](.*?)[\"][^>]*?>/";

    /**
     * PCRE-pattern for parsing Markdown image-links wrapped in anchor-links
     *
     * @deprecated 3.0.0
     */
    const REGEX_IMG_WRAPPING_LINK = '/\[(?\'image\'\!.*)\]\((?\'url\'https?:\/\/.*)\)/';

    /**
     * Execute before tests
     *
     * @return void
     */
    protected function _before()
    {
        include __DIR__ . '/../../vendor/autoload.php';
        $this->parsedown = new \Parsedown();
        $this->parsedown = $this->parsedown->setBreaksEnabled(true);
        $this->testData = [
            '![](image.jpg)',
            '![](image.jpg?classes=float-left)',
            '![Image](image.jpg)',
            '![Image](image.jpg?classes=float-left)',
            '![My Image](image.jpg)',
            '![My Image](image.jpg?classes=float-left)',
            '![My Image](image.jpg?classes=float-left,shadow)',
            '![My Image](image.jpg?id=special-id)',
            '![My Image](image.jpg?id=special-id&classes=float-left)',
            '![](IMG_20161229.jpg?resize=600,400)',
            '![](image.png?lightbox)'
        ];
        $this->testDataTitles = [
            '![](image.jpg "Title")',
            '![](image.jpg?classes=float-left "Title")',
            '![Image](image.jpg "Title")',
            '![Image](image.jpg?classes=float-left "Title")',
            '![My Image](image.jpg "Title")',
            '![My Image](image.jpg?link "Title")',
            '![My Image](image.jpg?classes=float-left "Title")',
            '![My Image](image.jpg?classes=float-left,shadow "Title")',
            '![My Image](image.jpg?id=special-id "Title")',
            '![My Image](image.jpg?id=special-id&classes=float-left "Title")',
            '![My Image](image.jpg?id=special-id&classes=float-left,shadow "Title")',
            '![](IMG_20161229.jpg?resize=600,400 "La boîte du Bookeen Cybo")',
            '![](image.png?lightbox "caption text")'
        ];
        $this->testDataExtra = array();
        foreach ($this->testData as $data) {
            $this->testDataExtra[] = $data . '{#id}';
            $this->testDataExtra[] = $data . '{.class}';
            $this->testDataExtra[] = $data . '{attr=ibute}';
            $this->testDataExtra[] = $data . '{#id .class}';
            $this->testDataExtra[] = $data . '{#id .class attr=ibute}';
            $this->testDataExtra[] = $data . '{#id .class1 .class2 attr=ibute}';
            $this->testDataExtra[] = $data . '{#id .class1 .class2 attr1=ibute1 attr2=ibute2}';
        }
        $this->testDataAnchorWrappers = array();
        foreach ($this->testData as $data) {
            $this->testDataAnchorWrappers[] = '[' . $data . '](http://google.com)';
            $this->testDataAnchorWrappers[] = '[' . $data . '](http://google.com/)';
            $this->testDataAnchorWrappers[] = '[' . $data . '](http://www.google.com)';
            $this->testDataAnchorWrappers[] = '[' . $data . '](http://www.google.com/)';
            $this->testDataAnchorWrappers[] = '[' . $data . '](https://google.com)';
            $this->testDataAnchorWrappers[] = '[' . $data . '](https://google.com/)';
            $this->testDataAnchorWrappers[] = '[' . $data . '](https://www.google.com)';
            $this->testDataAnchorWrappers[] = '[' . $data . '](https://www.google.com/)';
        }
    }

    /**
     * Execute after tests
     *
     * @return void
     */
    protected function _after()
    {
    }

    /**
     * Validate PCRE-patterns
     *
     * @return void
     */
    public function testValidateExpressions()
    {
        $Regex = new Regex();
        foreach ($Regex::all() as $pattern) {
            $this->assertTrue(pattern($pattern)->valid());
        }
    }

    /**
     * Test PCRE-pattern for parsing Markdown image-links
     *
     * @return void
     */
    public function testMarkdown()
    {
        $testData = array_merge($this->testData, $this->testDataTitles);
        foreach ($testData as $string) {
            $this->assertRegexp(Regex::markdownImage(), $string);
        }
    }

    /**
     * Test PCRE-pattern for parsing Markdown Extra image-links
     *
     * @return void
     */
    public function testMarkdownExtra()
    {
        foreach ($this->testDataExtra as $string) {
            $this->assertRegexp(Regex::markdownImage(), $string);
        }
    }

    /**
     * Test PCRE-pattern for parsing HTML img-tags
     *
     * @return void
     */
    public function testImages()
    {
        $testData = array_merge($this->testData, $this->testDataTitles);
        foreach ($testData as $string) {
            $parsed = $this->parsedown->text($string);
            $this->assertRegexp(Regex::HTMLImage(), $parsed);
        }
    }

    /**
     * Test PCRE-pattern for parsing HTML img-tags in p-tags
     *
     * @return void
     */
    public function testUnwrap()
    {
        $testData = array_merge($this->testData, $this->testDataTitles);
        foreach ($testData as $string) {
            $parsed = $this->parsedown->text("\n" . $string . "\n");
            $this->assertRegexp(Regex::HTMLParagraphWrapper(), $parsed);
        }
    }

    /**
     * Test PCRE-pattern for parsing title-attribute in HTML img-tags
     *
     * @deprecated 3.0.0
     *
     * @return void
     */
    public function testImageTitles()
    {
        return;
        foreach ($this->testDataTitles as $string) {
            $parsed = $this->parsedown->text($string);
            $this->assertRegexp($this::REGEX_IMG_TITLE, $parsed);
        }
    }

    /**
     * Test PCRE-pattern for parsing Markdown image-links wrapped in anchor-links
     *
     * @return void
     */
    public function testImageAnchorWrappers()
    {
        foreach ($this->testDataAnchorWrappers as $string) {
            $this->assertRegexp(Regex::markdownAnchorWrapper(), $string);
        }
    }
}
