<?php
namespace EightWire\Primer\Model\Cache;

use Magento\Framework\Module\ModuleListInterface;

class TypePlugin {

    /**
     * @var \EightWire\Primer\Api\PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * FlushAllCache constructor.
     *
     * @param \EightWire\Primer\Api\PageRepositoryInterface $pageRepository
     */
    public function __construct(
        \EightWire\Primer\Api\PageRepositoryInterface $pageRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->pageRepository = $pageRepository;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\PageCache\Model\Cache\Type $subject
     * @param callable $proceed
     * @param $mode
     * @param $tags
     */
    public function aroundClean(
        \Magento\PageCache\Model\Cache\Type $subject,
        callable $proceed,
        $mode = \Zend_Cache::CLEANING_MODE_ALL,
        array $tags = []
    ) {
        $proceed($mode, $tags);

        if ($mode === \Zend_Cache::CLEANING_MODE_ALL) {

            try {
                $this->pageRepository->flush();
            } catch(\Exception $e) {
                $this->logger->error('cannot flush primer urls with error: ' . $e->getMessage());
            }

        }
//        else {
//            /** @todo cache tags required on page log to clear by tag
//            if (count($tags)) {
//
//            }
//        }

        return $proceed();
    }
}
