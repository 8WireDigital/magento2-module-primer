<?php

namespace EightWire\Primer\Helper;

class Config
{

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }


    public function getSleepBetweenBatch()
    {
        return $this->scopeConfig->getValue(
            'system/cache_primer/sleep_between_batch',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getSleepWhenEmpty()
    {
        return $this->scopeConfig->getValue(
            'system/cache_primer/sleep_when_empty',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getBatchSize()
    {
        return $this->scopeConfig->getValue(
            'system/cache_primer/batch_size',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCrawlThreshold()
    {
        return $this->scopeConfig->getValue(
            'system/cache_primer/crawl_threshold',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    public function getCronEnabled()
    {
        return $this->scopeConfig->getValue(
            'system/cache_primer/cron_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCronOnComplete()
    {
        return $this->scopeConfig->getValue(
            'system/cache_primer/cron_on_complete',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCronMaxRuntime()
    {
        return $this->scopeConfig->getValue(
            'system/cache_primer/cron_max_run_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}