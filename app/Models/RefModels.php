<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TemporarySearchResults
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application where($value, $value)
 * @mixin \Eloquent
 * @property int $id
 * @property int $brand_id
 * @property int $model_id
 * @property int $body_id
 * @property int $generation_id
 * @property int $engine_id
 * @property string|null $parse_link
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefModels whereBodyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefModels whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefModels whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefModels whereEngineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefModels whereGenerationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefModels whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefModels whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefModels whereParseLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefModels whereUpdatedAt($value)
 */
class RefModels extends Model
{
    protected $table = 'ref_models';


}