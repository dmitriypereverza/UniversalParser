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
                $modifiedVars['url'],
                $modifiedVars['sparePartsCategoryName'],
                $modifiedVars['sparePartsName'],
                $modifiedVars['articul'],
                $modifiedVars['img']
            );
            Event::fire(new SparePartParserEvent(sprintf("Save spare part --> %s:%s",
                $modifiedVars['sparePartsCategoryName'],
                $modifiedVars['sparePartsName']
            )));
        }
    }
}
