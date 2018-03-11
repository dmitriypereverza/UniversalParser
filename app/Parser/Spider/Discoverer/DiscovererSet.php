<?php

namespace App\Parser\Spider\Discoverer;

use App\Models\Links;
use VDB\Spider\Discoverer\DiscovererInterface;
use VDB\Spider\Discoverer\DiscovererSet as VDBDiscovererSet;
use VDB\Spider\Resource;
use VDB\Spider\Filter\PreFetchFilterInterface;
use VDB\Spider\Uri\DiscoveredUri;

class DiscovererSet extends VDBDiscovererSet
{
    /**
     * @var Discoverer[]
     */
    private $discoverers = array();

    /** @var Filter[] */
    private $filters = array();

    /**
     * @var int maximum crawl depth
     */
    public $maxDepth = 3;

    /**
     * @var array the list of already visited URIs with the depth they were discovered on as value
     */
    public function __construct(array $discoverers = array())
    {
        foreach ($discoverers as $alias => $discoverer) {
            $this->set($discoverer, is_int($alias) ? null : $alias);
        }
    }

    /**
     * @param DiscoveredUri $uri
     *
     * Mark an Uri as already seen.
     *
     * If it already exists, it is not overwritten, since we want to keep the
     * first depth it was found at.
     */
    private function markSeen(DiscoveredUri $uri)
    {
        $uriString = $uri->normalize();
        $url = Links::getOrCreateLinkByUrl($uriString);
        $url->is_viewed = True;
        $url->save();
    }

    /**
     * @return bool Returns true if this URI was found at max depth
     */
    private function isAtMaxDepth(DiscoveredUri $uri)
    {
        return $uri->getDepthFound() === $this->maxDepth;
    }

    /**
     * @param Resource $resource
     * @return UriInterface[]
     */
    public function discover(Resource $resource)
    {
        $this->markSeen($resource->getUri());

        if ($this->isAtMaxDepth($resource->getUri())) {
            return [];
        }

        $discoveredUris = [];
        foreach ($this->discoverers as $discoverer) {
            $discoveredUris = array_merge($discoveredUris, $discoverer->discover($resource));
        }

        $this->normalize($discoveredUris);
        $this->removeDuplicates($discoveredUris);
        $this->filterAlreadySeen($discoveredUris);
        $this->filter($discoveredUris);

        foreach ($discoveredUris as $uri) {
            $uri->setDepthFound($resource->getUri()->getDepthFound() + 1);

            $parentUrl = Links::getOrCreateLinkByUrl($resource->getUri());
            $url = Links::getOrCreateLinkByUrl($uri);
            $url->referer()->attach($parentUrl->id);
            $url->text = $this->getLinkText($resource, $uri);
            $url->depth = $parentUrl->depth + 1;
            $url->save();
        }

        return $discoveredUris;
    }

    /**
     * Sets a discoverer.
     *
     * @param discovererInterface $discoverer The discoverer instance
     * @param string|null         $alias  An alias
     */
    public function set(DiscovererInterface $discoverer, $alias = null)
    {
        $this->discoverers[$discoverer->getName()] = $discoverer;
        if (null !== $alias) {
            $this->discoverers[$alias] = $discoverer;
        }
    }

    /**
     * @param PreFetchFilterInterface $filter
     */
    public function addFilter(PreFetchFilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @param UriInterface[] $discoveredUris
     */
    private function normalize(array &$discoveredUris)
    {
        foreach ($discoveredUris as &$uri) {
            $uri->normalize();
        }
    }

    /**
     * @param UriInterface[] $discoveredUris
     */
    private function filterAlreadySeen(array &$discoveredUris)
    {
        foreach ($discoveredUris as $k => &$uri) {
            if (Links::whereUrl($uri->toString())->get()->isNotEmpty()) {
                unset($discoveredUris[$k]);
            }
        }
    }

    /**
     * @param UriInterface[] $discoveredUris
     */
    private function filter(array &$discoveredUris)
    {
        foreach ($discoveredUris as $k => &$uri) {
            foreach ($this->filters as $filter) {
                if ($filter->match($uri)) {
                    unset($discoveredUris[$k]);
                }
            }
        }
    }

    /**
     * @param UriInterface[] $discoveredUris
     */
    private function removeDuplicates(array &$discoveredUris)
    {
        // make sure there are no duplicates in the list
        $tmp = array();
        /** @var Uri $uri */
        foreach ($discoveredUris as $k => $uri) {
            $tmp[$k] = $uri->toString();
        }

        // Find duplicates in temporary array
        $tmp = array_unique($tmp);

        // Remove the duplicates from original array
        foreach ($discoveredUris as $k => $uri) {
            if (!array_key_exists($k, $tmp)) {
                unset($discoveredUris[$k]);
            }
        }
    }

    private function getLinkText(Resource $resource, $uri)
    {
        $item = $resource->getCrawler()->filterXpath('//a[@href="' . $uri->getPath() . '"]');
        if ($item->count()) {
            return  trim($item->text());
        }
        return null;
    }
}
