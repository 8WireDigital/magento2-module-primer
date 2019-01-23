<?php

namespace EightWire\Primer\Test\Unit\Model;

use EightWire\Primer\Model\Page;

class PageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EightWire\Primer\Model\Crawler
     */
    protected $pageLogger;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->page = $objectManager->getObject('EightWire\Primer\Model\Page');
    }

    public function testIncrementPriority()
    {
        $this->page->setPriority(1);
        $this->page->incrementPriority();

        $this->assertEquals(2, $this->page->getPriority());
    }
}