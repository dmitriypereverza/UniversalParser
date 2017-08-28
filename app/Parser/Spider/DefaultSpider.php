<?php

namespace App\Parser\Spider;

use Symfony\Component\EventDispatcher\Event;
use VDB\Spider\Discoverer\XPathExpressionDiscoverer;
use VDB\Spider\Event\SpiderEvents;
use VDB\Spider\PersistenceHandler\PersistenceHandlerInterface;
use VDB\Spider\Spider;
use VDB\Spider\StatsHandler;

class DefaultSpider implements SpiderInterface {
    protected $url;
    private $isActive;
    private $urlTemplate;
    private $selectors;

    public function __construct(array $config) {
        $this->url = $config['url'];
        $this->isActive = $config['active'];
        $this->urlTemplate = $config['url_template'];
        $this->selectors = $config['selectors'];
    }

    public function crawl() {
        $spider = new Spider('http://dnor.ru');
        $spider->getDiscovererSet()->set(new XPathExpressionDiscoverer("//div[@class='row']//a[@class='app-ad-item__link']"));
        $spider->getDiscovererSet()->maxDepth = 2;
        $spider->getQueueManager()->maxQueueSize = 50;


        $spider->getDispatcher()->addListener(
            SpiderEvents::SPIDER_CRAWL_USER_STOPPED,
            function (Event $event) {
                echo "\nCrawl aborted by user.\n";
                exit();
            }
        );

        $statsHandler = new StatsHandler();
        $spider->getQueueManager()->getDispatcher()->addSubscriber($statsHandler);
        $spider->getDispatcher()->addSubscriber($statsHandler);

        $spider->crawl();

        echo "\n  ENQUEUED:  " . count($statsHandler->getQueued());
        echo "\n  SKIPPED:   " . count($statsHandler->getFiltered());
        echo "\n  FAILED:    " . count($statsHandler->getFailed());
        echo "\n  PERSISTED:    " . count($statsHandler->getPersisted());
// Finally we could do some processing on the downloaded resources
// In this example, we will echo the title of all resources
        echo "\n\nDOWNLOADED RESOURCES: ";
        foreach ($spider->getDownloader()->getPersistenceHandler() as $resource) {
            echo 'Страница:' . $this->getPageUrl($resource) . "\n";
            echo 'Бренд:' . $this->getSelectorContent($resource, $this->selectors['brand']) . "\n";
            echo 'Модель:' . $this->getSelectorContent($resource, $this->selectors['model']) . "\n";
            echo 'Деталь:' . $this->getSelectorContent($resource, $this->selectors['detail']) . "\n";
            echo "\n";
        }
    }

    public function stop() {
        // TODO: Implement stop() method.
    }

    public function getTitle() {
        return 'default';
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