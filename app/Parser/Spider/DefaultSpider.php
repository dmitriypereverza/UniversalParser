<?php

namespace App\Parser\Spider;

use App\Events\ParserInfoEvent;
use App\Models\TemporarySearchResults;
use App\Parser\Spider\Attributes\DetailPageParser;
use App\Parser\Spider\Attributes\TableParser;
use App\Parser\Spider\Filter\UriFilter as SimpleUriFilter;
use App\Parser\Spider\Filter\Prefetch\UriFilter;
use App\Parser\Spider\PersistenceHandler\DBPersistenceHandler;
use Symfony\Component\Console\Exception\InvalidArgumentException as InvalidArgumentExcept;
use VDB\Spider\Discoverer\XPathExpressionDiscoverer;
use VDB\Spider\Event\SpiderEvents;
use VDB\Spider\EventListener\PolitenessPolicyListener;
use VDB\Spider\Spider;
use VDB\Spider\StatsHandler;
use Illuminate\Support\Facades\Event as laravelEvent;

class DefaultSpider implements SpiderInterface {
    const DEFAULT_REQUEST_DELAY = 350;
    const DEFAULT_MAX_DEPTH = 4;
    const DEFAULT_QUERY_SIZE = 10;
    const DEFAULT_SESSION_RESULT = 100;

    private $id_session;
    private $countProcessedResults;
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

        $selectorParser = isset($this->config['selectors']['row']) ? new TableParser($this->config['selectors']) : new DetailPageParser($this->config['selectors']);
        $this->spider->getDownloader()->setPersistenceHandler(
            new DBPersistenceHandler(
                $selectorParser,
                $this->config['url'],
                $this->id_session,
                new SimpleUriFilter([$this->config['url_pattern_detail']])
            )
        );

        $this->setMaxDepth($this->config['max_depth'] ?? self::DEFAULT_MAX_DEPTH);
        $this->setMaxQueueSize($this->config['max_query_size'] ?? self::DEFAULT_QUERY_SIZE);
        $this->setReuestDelay($this->config['reuest_delay'] ?? self::DEFAULT_REQUEST_DELAY);
    }

    public function crawl() {
        $statsHandler = new StatsHandler();
        $this->spider->getQueueManager()->getDispatcher()->addSubscriber($statsHandler);

        try {
            $this->spider->crawl();
            laravelEvent::fire(new ParserInfoEvent(
                sprintf("PERSISTED: %d", count($statsHandler->getPersisted()))
            ));
            TemporarySearchResults::setNewVersion($this->config['url'], $this->id_session);
        } catch (\Exception $e) {
            laravelEvent::fire(new ParserInfoEvent(
                sprintf("Crawl ended with error: %s", $e->getMessage())
            ));
        }
    }

    private function getSpider() {
        $spider = new Spider($this->config['items_list_url']);
        $spider->getDiscovererSet()->set(new XPathExpressionDiscoverer($this->config['items_list_selector']));
        $spider->getDiscovererSet()->addFilter(new UriFilter([$this->config['url_pattern']]));
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

    private function getSessionId() {
        return md5($this->config['items_list_url'] . microtime(true));
    }

    public function onPostPersistEvent(callable $callback) {
        $this->spider->getDownloader()->getDispatcher()->addListener(
            SpiderEvents::SPIDER_CRAWL_POST_REQUEST,
            $callback
        );
    }

    /**
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }
}