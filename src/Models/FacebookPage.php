<?php

namespace Tokalink\FacebookAutopost\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookPage extends Model
{
    protected $fillable = [
        'facebook_account_id',
        'page_id',
        'name',
        'access_token',
        'category',
        'avatar',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the Facebook account that owns the page.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(FacebookAccount::class, 'facebook_account_id');
    }

    /**
     * Get the posts sent to this page.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(FacebookPost::class);
    }
}
