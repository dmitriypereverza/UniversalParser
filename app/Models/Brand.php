<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * App\Models\TemporarySearchResults
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application where($value, $value)
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $synonym_name
 * @property string $slug
 * @property int|null $r_brand_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Models[] $models
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Brand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Brand whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Brand whereRBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Brand whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Brand whereSynonymName($value)
 */
class Brand extends Model
{
    protected $table = 'car_makes';

    public function models()
    {
        return $this->hasMany('App\Models\Models', 'car_make_id');
    }
}