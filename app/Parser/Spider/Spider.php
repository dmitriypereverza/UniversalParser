<?php

namespace App\Parser\Spider;

use App\Events\ParserErrorEvent;
use App\Events\ParserInfoEvent;
use App\Models\TemporarySearchResults;
use App\Parser\Spider\Attributes\DetailPageParser;
use App\Parser\Spider\Attributes\TableParser;
use App\Parser\Spider\Filter\UriFilter as SimpleUriFilter;
use App\Parser\Spider\Filter\Prefetch\UriFilter;
use App\Parser\Spider\PersistenceHandler\DBPersistenceHandler;
use App\Parser\Spider\RequestHandler\GuzzleRequestWIthProxyHandler;
use Symfony\Component\Console\Exception\InvalidArgumentException as InvalidArgumentExcept;
use VDB\Spider\Discoverer\XPathExpressionDiscoverer;
use VDB\Spider\Event\SpiderEvents;
use VDB\Spider\EventListener\PolitenessPolicyListener;
use VDB\Spider\Spider as PhpSpider;
use VDB\Spider\StatsHandler;
use Illuminate\Support\Facades\Event as laravelEvent;

class Spider implements SpiderInterface
{
    private $id_session;
    private $countProcessedResults;
    private $config;
    /** @var PhpSpider $spider */
    private $spider;

    public function __construct(array $config)
    {
        if (!$config) {
            throw new InvalidArgumentExcept('Не переданы конфигурационные данные');
        }
        $this->config = $config;
        $this->spider = $this->getSpider();
        $this->id_session = $this->getSessionId();
        $this->countProcessedResults = 0;
//        $this->setRequestHandler();
        $this->setPersistenceHandler();

        $this->setMaxDepth($this->config['max_depth'] ?? self::DEFAULT_MAX_DEPTH);
        $this->setMaxQueueSize($this->config['max_query_size'] ?? self::DEFAULT_QUERY_SIZE);
        $this->setReuestDelay($this->config['request_delay'] ?? self::DEFAULT_REQUEST_DELAY);
    }

    public function crawl()
    {
        laravelEvent::fire(new ParserInfoEvent(sprintf("%s: Start crawl", $this->config['url'])));
        $statsHandler = $this->getStatHandler();
        try {
            $this->spider->crawl();
            laravelEvent::fire(new ParserInfoEvent(sprintf("%s: PERSISTED URL: %d; PERSISTED UNIQUE ELEMENTS: %d", $this->config['url'], count($statsHandler->getPersisted()), TemporarySearchResults::where('id_session', $this->id_session)->count())));
            TemporarySearchResults::setNewVersion($this->id_session);
            laravelEvent::fire(new ParserInfoEvent(sprintf("%s: End crawl", $this->config['url'])));
        } catch (\Exception $e) {
            TemporarySearchResults::deleteSessionResult($this->id_session);
            laravelEvent::fire(new ParserErrorEvent(sprintf("%s: Crawl finish with error: %s in %s line %s", $this->config['url'], $e->getMessage(), $e->getFile(), $e->getLine())));
        }
    }

    private function getSpider()
    {
        $spider = new PhpSpider($this->config['items_list_url']);
        $spider->getDiscovererSet()->set(new XPathExpressionDiscoverer($this->config['items_list_selector']));
        $spider->getDiscovererSet()->addFilter(new UriFilter([$this->config['url_pattern']]));
        $spider->getQueueManager()->setTraversalAlgorithm(InDBQueueManager::ALGORITHM_DEPTH_FIRST);
        return $spider;
    }

    /**
     * @param integer $delay Delay in milliseconds
     */
    private function setReuestDelay($delay)
    {
        $this->spider->getDownloader()->getDispatcher()->addListener(SpiderEvents::SPIDER_CRAWL_PRE_REQUEST, [
                new PolitenessPolicyListener($delay),
                'onCrawlPreRequest'
            ]);
    }

    /**
     * @param integer $maxDepth
     */
    private function setMaxDepth($maxDepth)
    {
        $this->spider->getDiscovererSet()->maxDepth = $maxDepth;
    }

    /**
     * @param integer $maxQueueSize
     */
    private function setMaxQueueSize($maxQueueSize)
    {
        $this->spider->getQueueManager()->maxQueueSize = $maxQueueSize;
    }

    private function getSessionId()
    {
        return md5($this->config['items_list_url'] . microtime(true));
    }

    public function onPostPersistEvent(callable $callback)
    {
        $this->spider->getDownloader()->getDispatcher()->addListener(SpiderEvents::SPIDER_CRAWL_POST_REQUEST, $callback);
    }

    public function getConfig()
    {
        return $this->config;
    }

    private function setRequestHandler()
    {
        $this->spider->getDownloader()->setRequestHandler(new GuzzleRequestWIthProxyHandler());
    }

    private function setPersistenceHandler()
    {
        $selectorParser = isset($this->config['selectors']['row']) ? new TableParser($this->config['selectors']) : new DetailPageParser($this->config['selectors']);
        $this->spider->getDownloader()->setPersistenceHandler(new DBPersistenceHandler($selectorParser, $this->config['url'], $this->id_session, new SimpleUriFilter([$this->config['url_pattern_detail']])));
    }

    /**
     * @return StatsHandler
     */
    private function getStatHandler()
    {
        $statsHandler = new StatsHandler();
        $this->spider->getDispatcher()->addSubscriber($statsHandler);
        return $statsHandler;
    }
}