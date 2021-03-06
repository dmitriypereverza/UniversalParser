<?php
namespace App\Parser\Spider\QueueManager;

use App\Models\Links;
use VDB\Spider\QueueManager\QueueManagerInterface;
use VDB\Spider\Uri\DiscoveredUri;
use Symfony\Component\EventDispatcher\GenericEvent;
use VDB\Spider\Event\SpiderEvents;
use VDB\Uri\Uri;

class LinksTreeQueueManager implements QueueManagerInterface
{
    use DispatcherTrait;
    /** @var int The traversal algorithm to use. Choose from the class constants
     */
    private $traversalAlgorithm = self::ALGORITHM_DEPTH_FIRST;

    /**
     * @param int $traversalAlgorithm Choose from the class constants
     * TODO: This should be extracted to a Strategy pattern
     */
    public function setTraversalAlgorithm($traversalAlgorithm)
    {
        $this->traversalAlgorithm = $traversalAlgorithm;
    }

    /**
     * @return int
     */
    public function getTraversalAlgorithm()
    {
        return $this->traversalAlgorithm;
    }

    /**
     * @param DiscoveredUri
     */
    public function addUri(DiscoveredUri $uri)
    {
        Links::getOrCreateLinkByUrl($uri);
        $this->getDispatcher()->dispatch(
            SpiderEvents::SPIDER_CRAWL_POST_ENQUEUE,
            new GenericEvent($this, array('uri' => $uri))
        );
    }

    public function next()
    {
        if ($this->traversalAlgorithm === static::ALGORITHM_DEPTH_FIRST) {
            $link = Links::getNotViewedUrl()->orderBy('id', 'desc')->limit(20)->get()->random(1)->first();
            return new DiscoveredUri(new Uri($link->url));
        } elseif ($this->traversalAlgorithm === static::ALGORITHM_BREADTH_FIRST) {
            $link = Links::getNotViewedUrl()->orderBy('id', 'asc')->limit(20)->get()->random(1)->first();
            return new DiscoveredUri(new Uri($link->url));
        } else {
            throw new \LogicException('No search algorithm set');
        }
    }
}
