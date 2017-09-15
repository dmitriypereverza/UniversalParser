<?php

namespace App\Parser\Spider\Attributes;

use VDB\Spider\Resource;

/**
 * @author d.pereverza@worksolutions.ru
 */
class DetailPageParser implements AttributeParserInterface
{
    /**  @var array $selectors */
    private $selectors;

    public function __construct($selectors)
    {
        $this->selectors = $selectors;
    }

    public function getSelectorsValue(Resource $resource)
    {
        $result['url'] = $resource->getCrawler()->getUri();
        foreach ($this->selectors as $key => $selector) {
            if (!$content = $this->getSelectorContent($resource, $selector)) {
                unset($result);
                continue;
            }
            $result[$key] = $content;
        }

        if (isset($result)) {
            return $result;
        }
    }

    /**
     * @param Resource $resource
     * @param $selector
     * @return string
     */
    private function getSelectorContent($resource, $selector)
    {
        $item = $resource->getCrawler()->filterXpath($selector);
        if ($item->count()) {
            return trim($item->html());
        }
    }

    /**
     * @return bool
     */
    public function isMultipleElements()
    {
        return false;
    }
}