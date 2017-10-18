<?php

namespace App\Parser\Spider\PersistenceHandler;

use App\Models\TemporarySearchResults;
use App\Parser\Spider\Attributes\AttributeParserInterface;
use App\Parser\Spider\Filter\UriFilter;
use VDB\Spider\PersistenceHandler\PersistenceHandlerInterface;
use VDB\Spider\Resource;

class DBPersistenceHandler implements PersistenceHandlerInterface
{
    /** @var Resource[] */
    private $resources = array();
    private $siteUrl;
    private $sessionId;
    /** @var $urlFilter UriFilter */
    private $urlFilter;
    /** @var $attributeParser AttributeParserInterface */
    private $attributeParser;

    public function __construct(AttributeParserInterface $attributeParser, $siteUrl, $sessionId, $urlFilter)
    {
        $this->siteUrl = $siteUrl;
        $this->sessionId = $sessionId;
        $this->urlFilter = $urlFilter;
        $this->attributeParser = $attributeParser;
    }

    public function count()
    {
        return count($this->resources);
    }

    public function persist(Resource $resource)
    {
        echo $resource->getUri() . "\n";
        if (!$this->urlFilter->match($resource->getUri())) {
            return;
        }
        $this->resources[] = $resource->getUri();
        if ($selectorVals = $this->attributeParser->getSelectorsValue($resource)) {
            if (!$this->attributeParser->isMultipleElements()) {
                $this->saveParseElement($selectorVals);
            }
            else {
                foreach ($selectorVals as $rowValues) {
                    $this->saveParseElement($rowValues);
                }
            }
        }
    }

    /**
     * @return Resource
     */
    public function current()
    {
        return current($this->resources);
    }

    /**
     * @return Resource|false
     */
    public function next()
    {
        next($this->resources);
    }

    /**
     * @return int
     */
    public function key()
    {
        return key($this->resources);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return (bool)current($this->resources);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        reset($this->resources);
    }

    /**
     * @param string $spiderId
     *
     * @return void
     */
    public function setSpiderId($spiderId)
    {
    }

    private function modifyFields($selectorVals)
    {
        $selectorVals['title'] = sprintf(
            '%s%s%s%s%s',
            isset($selectorVals['acticul']) ? $selectorVals['acticul'] . ' ' : '',
            isset($selectorVals['title']) ? $selectorVals['title'] . ' ' : '',
            isset($selectorVals['model']) ? $selectorVals['model'] . ' ' : '',
            isset($selectorVals['car_body_code']) ? $selectorVals['car_body_code'] . ' ' : '',
            isset($selectorVals['brand']) ? $selectorVals['brand'] . ' ' : ''
        );

        return $selectorVals;
    }

    private function saveParseElement($values)
    {
        $values = $this->modifyFields($values);
        TemporarySearchResults::insertIfNotExist($values, $this->siteUrl, $this->sessionId);
    }
}
