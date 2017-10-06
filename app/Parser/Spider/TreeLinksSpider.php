<?php

namespace App\Parser\Spider;

use App\Events\ParserTreeMakerEvent;
use App\Parser\Spider\Discoverer\DiscovererSet;
use App\Parser\Spider\Filter\Prefetch\UriFilter;
use App\Parser\Spider\PersistenceHandler\DBPersistenceLinkHandler;
use App\Parser\Spider\QueueManager\InDBQueueManager;
use App\Parser\Spider\RequestHandler\StatusDefinerWIthProxyHandler;
use Symfony\Component\Console\Exception\InvalidArgumentException as InvalidArgumentExcept;
use Symfony\Component\EventDispatcher\Event;
use VDB\Spider\Discoverer\XPathExpressionDiscoverer;
use VDB\Spider\Event\SpiderEvents;
use VDB\Spider\EventListener\PolitenessPolicyListener;
use VDB\Spider\Spider as PhpSpider;
use VDB\Spider\StatsHandler;
use Illuminate\Support\Facades\Event as laravelEvent;

class TreeLinksSpider implements SpiderInterface
{
    private $siteUrl;
    /** @var PhpSpider $spider */
    private $spider;

    public function __construct($siteUrl, $depth=null, $requestDelay = self::DEFAULT_REQUEST_DELAY)
    {
        if (!$siteUrl) {
            throw new InvalidArgumentExcept('Не передан адрес сайта');
        }
        $this->siteUrl = $siteUrl;
        $this->spider = $this->getSpider();
        $this->setRequestHandler();
        $this->setPersistenceHandler();

        $depth && $this->setMaxDepth($depth);
        $this->setReuestDelay($requestDelay);
    }

    public function crawl()
    {
        laravelEvent::fire(new ParserTreeMakerEvent(sprintf("%s: Start make tree", $this->siteUrl)));
        $statsHandler = $this->getStatHandler();
        try {
            $this->spider->crawl();
            laravelEvent::fire(new ParserTreeMakerEvent(sprintf("%s: PERSISTED URL: %d;", $this->siteUrl, count($statsHandler->getPersisted()))));
            laravelEvent::fire(new ParserTreeMakerEvent(sprintf("%s: End make tree", $this->siteUrl)));
        } catch (\Exception $e) {
            laravelEvent::fire(new ParserTreeMakerEvent(sprintf("%s: Make tree finish with error: %s in %s line %s", $this->siteUrl, $e->getMessage(), $e->getFile(), $e->getLine())));
        }
    }

    private function getSpider()
    {
        $spider = new PhpSpider($this->siteUrl);
        $spider->setDiscovererSet(new DiscovererSet());
        $spider->getDiscovererSet()->set(new XPathExpressionDiscoverer('.//a'));
        $spider->getDiscovererSet()->addFilter(new UriFilter(['/^' . str_replace("/", "\/", $this->siteUrl) . '/']));
        $spider->getQueueManager()->setTraversalAlgorithm(InDBQueueManager::ALGORITHM_BREADTH_FIRST);
        $spider->setQueueManager(new InDBQueueManager());
        $spider->getDispatcher()->addListener(
            SpiderEvents::SPIDER_CRAWL_USER_STOPPED,
            function (Event $event) {
                echo "\nCrawl aborted by user.\n";
                exit();
            }
        );

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

    public function onPostPersistEvent(callable $callback)
    {
        $this->spider->getDownloader()->getDispatcher()->addListener(SpiderEvents::SPIDER_CRAWL_POST_REQUEST, $callback);
    }

    private function setRequestHandler()
    {
        $this->spider->getDownloader()->setRequestHandler(new StatusDefinerWIthProxyHandler());
    }

    private function setPersistenceHandler()
    {
        $this->spider->getDownloader()->setPersistenceHandler(new DBPersistenceLinkHandler($this->siteUrl));
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