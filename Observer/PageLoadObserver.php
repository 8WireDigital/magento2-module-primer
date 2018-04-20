<?php
namespace EightWire\Primer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class PageLoadObserver implements ObserverInterface
{
    /**
     * @var \EightWire\Primer\Model\PageLogger
     */
    private $pageLogger;


    public function __construct(
        \EightWire\Primer\Model\PageLogger $pageLogger

    ) {
        $this->pageLogger = $pageLogger;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->pageLogger->log($observer->getEvent()->getRequest(), $observer->getEvent()->getResponse());
    }
}
