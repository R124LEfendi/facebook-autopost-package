<?php

namespace R124LEfendi\FacebookAutopost\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookAccount extends Model
{
    protected $fillable = [
        'user_id',
        'fb_user_id',
        'name',
        'email',
        'access_token',
        'avatar',
    ];

    /**
     * Get the user that owns the Facebook account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the pages associated with the Facebook account.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(FacebookPage::class);
    }
}
