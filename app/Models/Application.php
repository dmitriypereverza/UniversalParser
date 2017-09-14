<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property \Carbon\Carbon $created_at
 * @property int $id
 * @property string $name
 * @property string $key
 * @property string $secret
 * @property string $is_active
 * @property \Carbon\Carbon $updated_at
 */
class Application extends Model {
    protected $table = 'applications';

    public static function whereKeyAndSecret($appKey, $appSecret) {
        return self::where('key', '=', $appKey)
            ->where('secret', '=', $appSecret);
    }
}
