<?php

namespace App\Parser\Spider\PersistenceHandler;

use App\Events\SparePartParserEvent;
use App\Models\SpareParts;
use VDB\Spider\PersistenceHandler\PersistenceHandlerInterface;
use Illuminate\Support\Facades\Event;

class SparePartsPersistenceHandler extends DBPersistenceHandler implements PersistenceHandlerInterface
{
    public function modifyAndSaveParseElement($values)
    {
        if ($modifiedVars = $this->modifiedSelectorsValue($values)) {
            SpareParts::updateOrCreate(
                $this->config['items_list_url'],
                $values['url'],
                $values['sparePartsCategoryName'],
                $values['sparePartsName'],
                $values['articul'],
                $values['img']
            );
            Event::fire(new SparePartParserEvent(sprintf("Save spare part --> %s:%s",
                $values['sparePartsCategoryName'],
                $values['sparePartsName']
            )));
        }
    }

    public function modifiedSelectorsValue($selectorVals)
    {
        $result = [];
        $result['url'] = $selectorVals['url'];
        $selectors = $this->config['selectors'];
        unset($selectors['row']);
        foreach ($selectors as $key => $selector) {
            $content = $selectorVals[$key];
            $content = $this->getFilteredContent($selector, $content);
            if (!$content) {
                if (!array_key_exists('optional', $selector) || !$selector['optional']) {
                    unset($result);
                    break;
                }
            }
            $result[$key] = $content;
        }
        return $result ?? null;
    }
}
