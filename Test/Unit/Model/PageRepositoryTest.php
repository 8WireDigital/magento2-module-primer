<?php

namespace EightWire\Primer\Test\Unit\Model;

use EightWire\Primer\Model\PageRepository;

class PageLoggerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EightWire\Primer\Model\Crawler
     */
    protected $pageLogger;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->pageRepository = $objectManager->getObject('EightWire\Primer\Model\PageRepository');
    }

    public function test()
    {
        $this->assertTrue(true);
    }
}