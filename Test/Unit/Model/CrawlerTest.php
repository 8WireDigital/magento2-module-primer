<?php

namespace EightWire\Primer\Test\Unit\Model;

use EightWire\Primer\Model\Crawler;

class CrawlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EightWire\Primer\Model\Crawler
     */
    protected $crawler;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->crawler = $objectManager->getObject('EightWire\Primer\Model\Crawler');
    }

    public function testInvalidSetWhenComplete()
    {
        $this->expectException('\Exception');
        $this->crawler->setWhenComplete('invalid string');
    }

    public function testValidSetWhenComplete()
    {
        $this->assertInstanceOf(Crawler::class, $this->crawler->setWhenComplete(Crawler::WHEN_COMPLETE_STOP));
    }




}