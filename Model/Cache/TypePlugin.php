<?php
namespace EightWire\Primer\Model\Cache;

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
        \EightWire\Primer\Api\PageRepositoryInterface $pageRepository
    ) {
        $this->pageRepository = $pageRepository;
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
            $this->pageRepository->flush();
        } else {
            /** @todo flush by cache tag when not cleaning everything? */
            if (count($tags)) {
                var_dump($tags);
                die();
            }
        }
    }
}
