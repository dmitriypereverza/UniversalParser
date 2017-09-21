<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * App\Models\TemporarySearchResults
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application where($value, $value)
 * @mixin \Eloquent
 */
class Brand extends Model
{
    protected $table = 'car_makes';

    public function models()
    {
        return $this->hasMany('App\Models\Models', 'car_make_id');
    }
}