<?php

namespace App\Parser\Spider;

use App\Models\TemporarySearchResults;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\InvalidArgumentException as InvalidArgumentExcept;
use VDB\Spider\Discoverer\XPathExpressionDiscoverer;
use VDB\Spider\Event\SpiderEvents;
use VDB\Spider\EventListener\PolitenessPolicyListener;
use VDB\Spider\Spider;

class DefaultSpider implements SpiderInterface {
    const DEFAULT_REQUEST_DELAY = 350;
    const DEFAULT_MAX_DEPTH = 1;
    const DEFAULT_QUERY_SIZE = 10;
    const DEFAULT_SESSION_RESULT = 100;

    private $countProcessedResults;
    private $countSessionResult;
    private $config;
    private $id_session;
    /** @var Spider $spider  */
    private $spider;

    public function __construct(array $config) {
        if (!$config) {
            throw new InvalidArgumentExcept('Не переданы конфигурационные данные');
        }
        $this->config = $config;
        $this->id_session = $this->getSessionId();
        $this->spider = $this->getSpider();
        $this->countProcessedResults = 0;

        $this->setCountSessionResult($this->config['session_result'] ?: self::DEFAULT_SESSION_RESULT);
        $this->setMaxDepth($this->config['max_depth'] ?? self::DEFAULT_MAX_DEPTH);
        $this->setMaxQueueSize($this->config['max_query_size'] ?? self::DEFAULT_QUERY_SIZE);
        $this->setReuestDelay($this->config['reuest_delay'] ?? self::DEFAULT_REQUEST_DELAY);
    }

    public function crawl() {
        $querySize = $this->config['max_query_size'] ?? self::DEFAULT_QUERY_SIZE;
        while ($this->countProcessedResults + $querySize <= $this->countSessionResult) {
            try {
                $this->spider->crawl();
                if ($resourse = $this->getSearchResourses()) {
                    $this->insertToTempTable($resourse);
                }
            } catch (\Exception $e) {

            }

        }
    }

    private function getSpider() {
        $spider = new Spider($this->config['items_list_url']);
        $spider->getDiscovererSet()->set(new XPathExpressionDiscoverer($this->config['items_list_selector']));
        $spider->getDownloader()->setPersistenceHandler(
            new \VDB\Spider\PersistenceHandler\MemoryPersistenceHandler()
        );
        return $spider;
    }

    /**
     * @param integer $delay Delay in milliseconds
     */
    private function setReuestDelay($delay) {
        $this->spider->getDownloader()->getDispatcher()->addListener(
            SpiderEvents::SPIDER_CRAWL_PRE_REQUEST,
            [new PolitenessPolicyListener($delay), 'onCrawlPreRequest']
        );
    }

    /**
     * @param $resource
     * @param $selector
     * @return mixed
     */
    private function getSelectorContent($resource, $selector) {
        $item = $resource->getCrawler()->filterXpath($selector);
        if ($item->count()) {
            return $item->text();
        }
    }

    private function getPageUrl($resource) {
        return $resource->getCrawler()->getUri();
    }

    private function getSearchResourses() {
        $resultList = [];
        foreach ($this->spider->getDownloader()->getPersistenceHandler() as $resource) {
            $result['url'] = $this->getPageUrl($resource);
            foreach ($this->config['selectors'] as $key => $selector) {
                if (!$content = $this->getSelectorContent($resource, $selector)) {
                    unset($result);
                    continue;
                }
                $result[$key] = $content;
            }

            if (isset($result)) {
                $resultList[] = $result;
            }
        }

        return $resultList;
    }

    /**
     * @param array $results
     * @return bool
     */
    private function insertToTempTable($results) {
        if (!is_array($results)) {
            throw new InvalidArgumentException('Передан неверный аргумент при записи во временную таблицу');
        }
        foreach ($results as $result) {
            $tmpTable = new TemporarySearchResults();
            $tmpTable->config_site_name = $this->config['url'];
            $tmpTable->id_session = $this->id_session;
            $tmpTable->content = json_encode($result);
            $tmpTable->hash = md5(serialize($result));

            if ($tmpTable->save()) {
                $this->countProcessedResults ++;
            }
        }
    }

    /**
     * @param integer $maxDepth
     */
    private function setMaxDepth($maxDepth) {
        $this->spider->getDiscovererSet()->maxDepth = $maxDepth;
    }

    /**
     * @param integer $maxQueueSize
     */
    private function setMaxQueueSize($maxQueueSize) {
        $this->spider->getQueueManager()->maxQueueSize = $maxQueueSize;
    }

    /**
     * @return string
     */
    private function getSessionId(): string {
        return md5($this->config['items_list_url'] . microtime(true));
    }

    private function setCountSessionResult($count) {
        $this->countSessionResult = $count;
    }
}