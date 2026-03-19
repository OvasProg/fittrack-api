<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Keeps a history of what admins do in the app.
 *
 * Whenever an admin changes a setting, deletes a user, or updates a plan,
 * we save it here. This helps us see who did what if something goes wrong.
 *
 * @property int $id
 * @property int $admin_id
 * @property string $action
 * @property string|null $details
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $admin
 */
class AuditLog extends Model
{
    protected $fillable = ['admin_id', 'action', 'details'];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id')->select(['id', 'name', 'email']);
    }
}
