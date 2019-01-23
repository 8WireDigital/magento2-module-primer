<?php

namespace EightWire\Primer\Test\Unit\Model;

use EightWire\Primer\Model\PageLogger;

class PageLoggerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EightWire\Primer\Model\Crawler
     */
    protected $pageLogger;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->pageLogger = $objectManager->getObject('EightWire\Primer\Model\PageLogger');
    }

    public function test()
    {
        $this->assertTrue(true);
    }
}