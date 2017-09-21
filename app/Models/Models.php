<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TemporarySearchResults
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application where($value, $value)
 * @mixin \Eloquent
 */
class Models extends Model
{
    protected $table = 'car_modifications';

    public function brand()
    {
        return $this->belongsTo('App\Brand');
    }
}