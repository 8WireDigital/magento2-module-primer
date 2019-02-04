<?php

namespace EightWire\Primer\Api;

interface CrawlerInterface
{
    const WHEN_COMPLETE_SLEEP = 'sleep';
    const WHEN_COMPLETE_STOP = 'stop';


    /**
     * @return mixed
     */
    public function getWhenComplete();

    /**
     * @param $whenComplete
     * @return mixed
     */
    public function setWhenComplete($whenComplete);

    /**
     * @return mixed
     */
    public function getSleepBetweenBatch();

    /**
     * @param $time
     * @return mixed
     */
    public function setSleepBetweenBatch($time);

    /**
     * @return mixed
     */
    public function getSleepWhenEmpty();

    /**
     * @param $time
     * @return mixed
     */
    public function setSleepWhenEmpty($time);

    /**
     * @return mixed
     */
    public function getBatchSize();

    /**
     * @param $size
     * @return mixed
     */
    public function setBatchSize($size);

    /**
     * @return mixed
     */
    public function getMaxRunTime();

    /**
     * @param $time
     * @return mixed
     */
    public function setMaxRunTime($time);

    /**
     * @return mixed
     */
    public function getCrawlThreshold();

    /**
     * @param $priorty
     * @return mixed
     */
    public function setCrawlThreshold($priorty);
}
