<?php

namespace App\Parser\Spider;

use App\Models\TemporarySearchResults;
use App\Parser\Spider\PersistenceHandler\DBPersistenceHandler;
use App\Parser\Spider\PersistenceHandler\MemoryPersistenceHandler;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\InvalidArgumentException as InvalidArgumentExcept;
use Symfony\Component\EventDispatcher\Event;
use VDB\Spider\Discoverer\XPathExpressionDiscoverer;
use VDB\Spider\Event\SpiderEvents;
use VDB\Spider\EventListener\PolitenessPolicyListener;
use VDB\Spider\Filter\Prefetch\UriFilter;
use VDB\Spider\QueueManager\InMemoryQueueManager;
use VDB\Spider\Spider;
use VDB\Spider\StatsHandler;

class DefaultSpider implements SpiderInterface {
    const DEFAULT_REQUEST_DELAY = 350;
    const DEFAULT_MAX_DEPTH = 3;
    const DEFAULT_QUERY_SIZE = 10;
    const DEFAULT_SESSION_RESULT = 100;

    private $id_session;
    private $countProcessedResults;
    private $countSessionResult;
    private $config;
    /** @var Spider $spider  */
    private $spider;

    public function __construct(array $config) {
        if (!$config) {
            throw new InvalidArgumentExcept('Не переданы конфигурационные данные');
        }
        $this->config = $config;
        $this->spider = $this->getSpider();
        $this->id_session = $this->getSessionId();

        $this->countProcessedResults = 0;

        $this->spider->getDownloader()->setPersistenceHandler(
            new DBPersistenceHandler(
                $this->config['selectors'],
                $this->config['url'],
                $this->id_session
            )
        );

        $this->setCountSessionResults($this->config['session_result'] ?: self::DEFAULT_SESSION_RESULT);
        $this->setMaxDepth($this->config['max_depth'] ?? self::DEFAULT_MAX_DEPTH);
        $this->setMaxQueueSize($this->config['max_query_size'] ?? self::DEFAULT_QUERY_SIZE);
        $this->setReuestDelay($this->config['reuest_delay'] ?? self::DEFAULT_REQUEST_DELAY);
    }

    public function crawl() {
        $statsHandler = new StatsHandler();
        $this->spider->getQueueManager()->getDispatcher()->addSubscriber($statsHandler);
        $this->spider->getDispatcher()->addSubscriber($statsHandler);

        try {
            $this->spider->crawl();

            echo "\n\nSPIDER ID: " . $statsHandler->getSpiderId();
            echo "\n  ENQUEUED:  " . count($statsHandler->getQueued());
            echo "\n  SKIPPED:   " . count($statsHandler->getFiltered());
            echo "\n  FAILED:    " . count($statsHandler->getFailed());
            echo "\n  PERSISTED:    " . count($statsHandler->getPersisted());

            TemporarySearchResults::setVersion(1, $this->id_session);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    private function getSpider() {
        $spider = new Spider($this->config['items_list_url']);
        $spider->getDiscovererSet()->set(new XPathExpressionDiscoverer($this->config['items_list_selector']));
        $spider->getQueueManager()->setTraversalAlgorithm(InMemoryQueueManager::ALGORITHM_DEPTH_FIRST);
        $spider->getDiscovererSet()->addFilter(new UriFilter(['/http:\/\/razbor-nt.ru\/.+\/.+\/(.+).html/']));
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


    private function setCountSessionResults($count) {
        $this->countSessionResult = $count;
    }

    private function getSessionId() {
        return md5($this->config['items_list_url'] . microtime(true));
    }
}