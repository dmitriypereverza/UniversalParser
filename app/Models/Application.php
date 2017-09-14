<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Application
 *
 * @property \Carbon\Carbon $created_at
 * @property int $id
 * @property string $name
 * @property string $key
 * @property string $secret
 * @property string $is_active
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Application extends Model {
    protected $table = 'applications';

    public static function whereKeyAndSecret($appKey, $appSecret) {
        return self::where('key', '=', $appKey)
            ->where('secret', '=', $appSecret);
    }
}
