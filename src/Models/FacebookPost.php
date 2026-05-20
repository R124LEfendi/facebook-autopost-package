<?php

namespace R124LEfendi\FacebookAutopost\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookPost extends Model
{
    protected $fillable = [
        'user_id',
        'facebook_page_id',
        'message',
        'link',
        'image_path',
        'fb_post_id',
        'status',
        'error_message',
        'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    /**
     * Get the user who created the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the page this post was sent to.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class, 'facebook_page_id');
    }
}
