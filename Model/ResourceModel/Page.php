<?php
namespace EightWire\Primer\Model\ResourceModel;


class Page extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('eightwire_primer_page', 'page_id');
    }
}
