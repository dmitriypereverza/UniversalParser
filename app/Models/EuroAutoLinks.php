<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EuroAutoLinks
 *
 * @property int $id
 * @property string $root_model_link
 * @property int|null $is_recived
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EuroAutoLinks whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EuroAutoLinks whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EuroAutoLinks whereIsRecived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EuroAutoLinks whereRootModelLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EuroAutoLinks whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EuroAutoLinks extends Model
{
    protected $table = 'euroavto_links';
}