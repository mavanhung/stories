<?php

namespace Botble\Comment\Models;

use Botble\Base\Models\BaseModel;
use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
class Comment extends BaseModel
{
    use EnumCastable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'comments';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'posts_id',
        'star',
        'comment',
        'images'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];

    // public function registerMediaCollections(): void
    // {
    //     $this->addMediaCollection('comments')->singleFile();
    // }

    // public function getImageAttribute(){
    //     return $this->getFirstMedia('comments') ? $this->getFirstMedia('comments')->getUrl() : '';
    // }
}
