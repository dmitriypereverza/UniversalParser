<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Links
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application where($value, $value)
 * @mixin \Eloquent
 * @property int $id
 * @property string $url
 * @property string $title
 * @property string $text
 * @property string $server_response_code
 * @property int $parent_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Links[] $referer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Links whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Links whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Links whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Links whereServerResponseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Links whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Links whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Links whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Links whereUrl($value)
 */
class Links extends Model
{
    protected $table = 'links';

    /**
     * Get the comments for the blog post.
     */
    public function referer()
    {
        return $this->belongsToMany('\App\Models\Links', 'parent_id');
    }

    public static function getOrCreateLinkByUrl($uri)
    {
        $link = Links::whereUrl($uri)->first();
        if (!$link) {
            $link = new Links();
            $link->url = $uri->toString();
            $link->save();
        }
        return $link;
    }

    public static function getNotViewedUrl()
    {
        return Links::where(['is_viewed' => null]);
    }
}
