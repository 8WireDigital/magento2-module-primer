<?php
namespace EightWire\Primer\Model;

use EightWire\Primer\Api\Data\PageInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Page extends \Magento\Framework\Model\AbstractModel implements PageInterface
{
    const CACHE_TAG = 'eightwire_primer_page';


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []


    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('EightWire\Primer\Model\ResourceModel\Page');
    }

    public function getPageId()
    {
        return $this->getData(self::PAGE_ID);
    }

    public function setPageId($pageId)
    {
        $this->setData(self::PAGE_ID, $pageId);
        return $this;
    }

    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    public function setPath($path)
    {
        $this->setData(self::PATH, $path);
        return $this;
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    public function getPriority()
    {
        return $this->getData(self::PRIORITY);
    }

    public function setPriority($priority)
    {
        $this->setData(self::PRIORITY, $priority);
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }

    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    public function setStoreId($storeId)
    {
        $this->setData(self::STORE_ID, $storeId);
        return $this;
    }

    public function incrementPriority()
    {
        $priority = $this->getPriority();
        $priority++;
        $this->setPriority($priority);
    }

    public function getStoreUrl()
    {
        $basePath = rtrim($this->storeManager->getStore($this->getStoreId())->getBaseUrl(), "/");
        return $basePath.$this->getPath();
    }

    public function getCookieDomain()
    {
        $code = $this->storeManager->getStore($this->getStoreId())->getCode();

        return $this->scopeConfig->getValue(
            'web/cookie/cookie_domain',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $code
        );
    }

    public function getMagentoVary()
    {
        return $this->getData(self::MAGENTO_VARY);
    }

    public function setMagentoVary($value)
    {
        $this->setData(self::MAGENTO_VARY, $value);
        return $this;
    }
}
