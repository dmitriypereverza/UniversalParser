<?php

namespace App\Parser\Spider;

use Symfony\Component\EventDispatcher\Event;
use VDB\Spider\Discoverer\XPathExpressionDiscoverer;
use VDB\Spider\Event\SpiderEvents;
use VDB\Spider\EventListener\PolitenessPolicyListener;
use VDB\Spider\Spider;
use VDB\Spider\StatsHandler;

class DefaultSpider implements SpiderInterface {
    /** @var Spider $spider  */
    private $spider;
    private $itemsListSelector;
    private $itemsListUrl;
    private $isActive;
    private $selectors;

    public function __construct(array $config) {
        $this->itemsListUrl = $config['items_list_url'];
        $this->isActive = $config['active'];
        $this->itemsListSelector = $config['items_list_selector'];
        $this->selectors = $config['selectors'];
        $this->spider = $this->getSpider();
        $this->setHandlers();
    }

    public function crawl() {
        $this->spider->crawl();

        $this->saveInTemporaryTable();
    }

    public function stop() {
        // TODO: Implement stop() method.
    }

    private function getSpider() {
        $spider = new Spider($this->itemsListUrl);
        $spider->getDiscovererSet()->set(new XPathExpressionDiscoverer($this->itemsListSelector));
        $spider->getDiscovererSet()->maxDepth = 1;
        $spider->getQueueManager()->maxQueueSize = 10;
    }

    private function setHandlers() {
        $this->spider->getDispatcher()->addListener(
            SpiderEvents::SPIDER_CRAWL_USER_STOPPED,
            function (Event $event) {
                echo "\nCrawl aborted by user.\n";
                exit();
            }
        );
        $this->spider->getDownloader()->getDispatcher()->addListener(
            SpiderEvents::SPIDER_CRAWL_PRE_REQUEST,
            [new PolitenessPolicyListener(100), 'onCrawlPreRequest']
        );
        $statsHandler = new StatsHandler();
        $this->spider->getQueueManager()->getDispatcher()->addSubscriber($statsHandler);
        $this->spider->getDispatcher()->addSubscriber($statsHandler);
    }

    private function getSelectorContent($resource, $selector) {
        $item = $resource->getCrawler()->filterXpath($selector);
        if ($item->count()) {
            return $item->text();
        }
    }

    private function getPageUrl($resource) {
        return $resource->getCrawler()->getUri();
    }

    private function saveInTemporaryTable() {
        // TODO remove console output
        foreach ($this->spider->getDownloader()->getPersistenceHandler() as $resource) {
            echo 'Url: ' . $this->getPageUrl($resource) . "\n";
            foreach ($this->selectors as $key => $selector) {
                echo $key . ': ' . $this->getSelectorContent($resource, $selector) . "\n";
            }
            echo "\n";
        }
    }
}