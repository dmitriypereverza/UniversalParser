<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Proxy
 *
 * @property int $id
 * @property string $url
 * @property string $name
 * @property int $isLast
 * @property int $isAvailable
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Proxy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Proxy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Proxy whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Proxy whereIsLast($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Proxy whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Proxy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Proxy whereUrl($value)
 * @mixin \Eloquent
 */
class Proxy extends Model
{
    protected $table = 'proxy';
}