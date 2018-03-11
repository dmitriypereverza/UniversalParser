<?php
namespace App\Parser\Spider\PersistenceHandler;

use App\Events\SparePartParserEvent;
use App\Models\SpareParts;
use Illuminate\Support\Facades\Event;

class SparePartsPersistenceHandler extends DBPersistenceHandler
{
    public function modifyAndSaveParseElement($values)
    {
        if ($modifiedVars = $this->modifiedSelectorsValue($values)) {
            SpareParts::updateOrCreate(
                $this->config['items_list_url'],
                $modifiedVars['url'],
                $modifiedVars['sparePartsCategoryName'],
                $modifiedVars['title'],
                $modifiedVars['sparePart'],
                $modifiedVars['articul'],
                $modifiedVars['img']
            );
            Event::fire(new SparePartParserEvent(sprintf("Save spare part --> %s:%s",
                $modifiedVars['sparePartsCategoryName'],
                $modifiedVars['title']
            )));
        }
    }
}
