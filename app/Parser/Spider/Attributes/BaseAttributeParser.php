<?php
namespace App\Parser\Spider\Attributes;
use Symfony\Component\DomCrawler\Crawler;
use \VDB\Spider\Resource;

/**
 * @author d.pereverza@worksolutions.ru
 */
abstract class BaseAttributeParser implements AttributeParserInterface
{
    /**  @var array $selectors */
    protected $selectors;

    public function __construct($selectors)
    {
        $this->selectors = $selectors;
    }

    abstract public function getSelectorsValue(Resource $resource);
    abstract public function isMultipleElements();

    /**
     * @param Crawler $crawler
     * @param $selector
     * @return string
     */
    protected function getSelectorContent(Crawler $crawler, $selector)
    {
        $item = $crawler->filterXpath($selector);
        if ($item->count()) {
            $trimmedText = trim($item->text());
            return preg_replace('/\s+/', ' ', $trimmedText);
        }
    }
}