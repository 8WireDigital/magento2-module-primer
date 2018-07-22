<?php
namespace EightWire\Primer\Model\ResourceModel;


class Page extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('eightwire_primer_page', 'page_id');
    }
}
