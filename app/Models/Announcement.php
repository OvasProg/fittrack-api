<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A global message or alert shown to users in the app.
 *
 * Admins use this to share important news, like upcoming maintenance
 * or a new workout feature. The 'is_active' flag lets admins hide
 * the message without having to delete it entirely.
 *
 * @property int $id
 * @property string $title
 * @property string $message
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Announcement extends Model
{
    protected $fillable = ['title', 'message', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
