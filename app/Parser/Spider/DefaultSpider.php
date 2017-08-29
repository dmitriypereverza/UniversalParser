<?php

namespace App\Parser\Spider;

use Symfony\Component\EventDispatcher\Event;
use VDB\Spider\Discoverer\XPathExpressionDiscoverer;
use VDB\Spider\Event\SpiderEvents;
use VDB\Spider\EventListener\PolitenessPolicyListener;
use VDB\Spider\PersistenceHandler\PersistenceHandlerInterface;
use VDB\Spider\Spider;
use VDB\Spider\StatsHandler;

class DefaultSpider implements SpiderInterface {
    private $itemsListSelector;
    private $itemsListUrl;
    private $isActive;
    private $selectors;

    public function __construct(array $config) {
        $this->itemsListUrl = $config['items_list_url'];
        $this->isActive = $config['active'];
        $this->itemsListSelector = $config['items_list_selector'];
        $this->selectors = $config['selectors'];
    }

    public function crawl() {
        $spider = new Spider($this->itemsListUrl);
        $spider->getDiscovererSet()->set(new XPathExpressionDiscoverer($this->itemsListSelector));
        $spider->getDiscovererSet()->maxDepth = 1;
        $spider->getQueueManager()->maxQueueSize = 10;

        $spider->getDispatcher()->addListener(
            SpiderEvents::SPIDER_CRAWL_USER_STOPPED,
            function (Event $event) {
                echo "\nCrawl aborted by user.\n";
                exit();
            }
        );
        $politenessPolicyEventListener = new PolitenessPolicyListener(100);
        $spider->getDownloader()->getDispatcher()->addListener(
            SpiderEvents::SPIDER_CRAWL_PRE_REQUEST,
            [$politenessPolicyEventListener, 'onCrawlPreRequest']
        );

        $statsHandler = new StatsHandler();
        $spider->getQueueManager()->getDispatcher()->addSubscriber($statsHandler);
        $spider->getDispatcher()->addSubscriber($statsHandler);

        $spider->crawl();

        echo "\n  ENQUEUED:  " . count($statsHandler->getQueued());
        echo "\n  SKIPPED:   " . count($statsHandler->getFiltered());
        echo "\n  FAILED:    " . count($statsHandler->getFailed());
        echo "\n  PERSISTED:    " . count($statsHandler->getPersisted());
        echo "\n\nDOWNLOADED RESOURCES: ";

        foreach ($spider->getDownloader()->getPersistenceHandler() as $resource) {
            echo 'Страница: ' . $this->getPageUrl($resource) . "\n";
            echo 'Бренд: ' . $this->getSelectorContent($resource, $this->selectors['brand']) . "\n";
            echo 'Модель: ' . $this->getSelectorContent($resource, $this->selectors['model']) . "\n";
            echo 'Деталь: ' . $this->getSelectorContent($resource, $this->selectors['detail']) . "\n";
            echo "\n";
        }
    }

    public function stop() {
        // TODO: Implement stop() method.
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
}