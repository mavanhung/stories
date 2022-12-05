<?php

namespace Botble\Comment\Models;

use Botble\Base\Models\BaseModel;

class CommentTranslation extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'comments_translations';

    /**
     * @var array
     */
    protected $fillable = [
        'lang_code',
        'comments_id',
        'name',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;
}
