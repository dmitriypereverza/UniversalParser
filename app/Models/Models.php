<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TemporarySearchResults
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application where($value, $value)
 * @mixin \Eloquent
 * @property int $id
 * @property int $car_make_id
 * @property string $name
 * @property string $slug
 * @property string $synonym_name
 * @property int|null $r_model_id
 * @property-read \App\Models\Brand $brand
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Models whereCarMakeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Models whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Models whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Models whereRModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Models whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Models whereSynonymName($value)
 */
class Models extends Model
{
    protected $table = 'car_modifications';

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}