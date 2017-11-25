<?php

/**
 * ImgCaptions-class Unit Tests
 *
 * Class ImgCaptionsPlugin
 * 
 * @package Grav\Plugin
 * @return  void
 * @author  Ole Vik <git@olevik.net>
 * @license MIT License by Ole Vik
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
     */
    const REGEX_MARKDOWN_LINK = '/!\[(?\'alt\'.*)\]\s?\((?\'file\'.*)(?\'ext\'.png|.gif|.jpg|.jpeg)(?\'grav\'\??(?\'type\'id|classes)\=.*[^"])?\s*(?:\"(?\'title\'.*)\")*\)/';

    /**
     * PCRE-pattern for parsing HTML img-tags
     */
    const REGEX_IMG = "/(<img(?:(\s*(class)\s*=\s*\x22([^\x22]+)\x22*)+|[^>]+?)*>)/";

    /**
     * PCRE-pattern for parsing HTML img-tags in p-tags
     */
    const REGEX_IMG_P = "/<p>\s*?(<a .*<img.*<\/a>|<img.*)?\s*<\/p>/";

    /**
     * PCRE-pattern for parsing title-attribute in HTML img-tags
     */
    const REGEX_IMG_TITLE = "/<img[^>]*?title[ ]*=[ ]*[\"](.*?)[\"][^>]*?>/";

    /**
     * Execute before tests
     *
     * @return void
     */
    protected function _before()
    {
        $this->parsedown = new Parsedown();
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
            '![My Image](image.jpg?id=special-id&classes=float-left)'
        ];
        $this->testDataTitles = [
            '![](image.jpg "Title")',
            '![](image.jpg?classes=float-left "Title")',
            '![Image](image.jpg "Title")',
            '![Image](image.jpg?classes=float-left "Title")',
            '![My Image](image.jpg "Title")',
            '![My Image](image.jpg?classes=float-left "Title")',
            '![My Image](image.jpg?classes=float-left,shadow "Title")',
            '![My Image](image.jpg?id=special-id "Title")',
            '![My Image](image.jpg?id=special-id&classes=float-left "Title")',
            '![My Image](image.jpg?id=special-id&classes=float-left,shadow "Title")'
        ];
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
     * Test PCRE-pattern for parsing Markdown image-links
     *
     * @return void
     */
    public function testMarkdown()
    {
        $testData = array_merge($this->testData, $this->testDataTitles);
        foreach ($testData as $string) {
            $this->assertRegexp($this::REGEX_MARKDOWN_LINK, $string);
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
            $this->assertRegexp($this::REGEX_IMG, $parsed);
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
            $this->assertRegexp($this::REGEX_IMG_P, $parsed);
        }
    }

    /**
     * Test PCRE-pattern for parsing title-attribute in HTML img-tags
     *
     * @return void
     */
    public function testImageTitles()
    {
        foreach ($this->testDataTitles as $string) {
            $parsed = $this->parsedown->text($string);
            $this->assertRegexp($this::REGEX_IMG_TITLE, $parsed);
        }
    }
}