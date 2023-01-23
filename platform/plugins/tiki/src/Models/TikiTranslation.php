<?php

namespace Botble\Tiki\Models;

use Botble\Base\Models\BaseModel;

class TikiTranslation extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tikis_translations';

    /**
     * @var array
     */
    protected $fillable = [
        'lang_code',
        'tikis_id',
        'name',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;
}
