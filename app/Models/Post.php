<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use MoonShine\Models\MoonshineUser;

class Post extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'telegram_message_id',
        'user_id',
        'company_id',
        'url_title',
        'tg_chat_title'
    ];
    protected $table = 'tg_posts';

    public function company():BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function media():HasOne
    {
        return $this->hasOne(Media::class);
    }

    public function button():HasMany
    {
        return $this->hasMany(BotButton::class);
    }
    public function posts():BelongsToMany
    {
        // Укажите соответствие между столбцами в модели и в базе данных.
        return $this->belongsToMany(PostUser::class, 'tg_post_users', 'tg_post_id', 'tg_user_id');
    }

    public function bot():BelongsTo
    {
        return $this->belongsTo(TgBot::class);
    }
    public function group():BelongsTo
    {
        return $this->belongsTo(TgGroup::class);
    }


}
